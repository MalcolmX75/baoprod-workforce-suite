<?php

namespace BaoProd\Workforce\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebhookService
{
    /**
     * Registered webhook endpoints
     */
    protected array $webhooks = [];

    public function __construct()
    {
        $this->webhooks = config('webhooks.endpoints', []);
    }

    /**
     * Trigger webhook for specific event
     */
    public function trigger(string $event, array $data, int $tenantId = null): void
    {
        $tenantId = $tenantId ?? $this->getCurrentTenantId();
        
        $relevantWebhooks = $this->getWebhooksForEvent($event, $tenantId);

        foreach ($relevantWebhooks as $webhook) {
            $this->sendWebhook($webhook, $event, $data);
        }
    }

    /**
     * Send webhook to endpoint
     */
    private function sendWebhook(array $webhook, string $event, array $data): void
    {
        try {
            $payload = [
                'event' => $event,
                'timestamp' => now()->toISOString(),
                'data' => $data,
                'tenant_id' => $this->getCurrentTenantId(),
            ];

            $response = Http::timeout(30)
                          ->withHeaders([
                              'Content-Type' => 'application/json',
                              'User-Agent' => 'BaoProd-Workforce-Webhook/1.0',
                              'X-Webhook-Signature' => $this->generateSignature($payload, $webhook['secret'] ?? ''),
                          ])
                          ->post($webhook['url'], $payload);

            if ($response->successful()) {
                Log::info('Webhook sent successfully', [
                    'url' => $webhook['url'],
                    'event' => $event,
                    'status' => $response->status(),
                ]);
            } else {
                Log::warning('Webhook failed', [
                    'url' => $webhook['url'],
                    'event' => $event,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Webhook exception', [
                'url' => $webhook['url'],
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get webhooks for specific event and tenant
     */
    private function getWebhooksForEvent(string $event, int $tenantId): array
    {
        return array_filter($this->webhooks, function ($webhook) use ($event, $tenantId) {
            $eventMatches = in_array($event, $webhook['events']) || in_array('*', $webhook['events']);
            $tenantMatches = !isset($webhook['tenant_id']) || $webhook['tenant_id'] === $tenantId;
            
            return $eventMatches && $tenantMatches && ($webhook['active'] ?? true);
        });
    }

    /**
     * Generate webhook signature
     */
    private function generateSignature(array $payload, string $secret): string
    {
        if (empty($secret)) {
            return '';
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $jsonPayload, $secret);
    }

    /**
     * Register webhook endpoint
     */
    public function register(string $url, array $events, array $options = []): void
    {
        $this->webhooks[] = array_merge([
            'url' => $url,
            'events' => $events,
            'active' => true,
        ], $options);
    }

    /**
     * Get current tenant ID
     */
    private function getCurrentTenantId(): ?int
    {
        return auth()->user()->tenant_id ?? 1;
    }

    /**
     * Available webhook events
     */
    public static function getAvailableEvents(): array
    {
        return [
            // Job events
            'job.created',
            'job.updated', 
            'job.deleted',
            'job.published',
            'job.unpublished',
            
            // Application events
            'application.created',
            'application.updated',
            'application.status_changed',
            'application.accepted',
            'application.rejected',
            
            // Contract events
            'contract.created',
            'contract.signed',
            'contract.activated',
            'contract.terminated',
            
            // Employee events
            'employee.hired',
            'employee.terminated',
            'employee.updated',
            
            // Timesheet events
            'timesheet.submitted',
            'timesheet.approved',
            'timesheet.rejected',
            
            // Payroll events
            'payroll.calculated',
            'payroll.approved',
            'payroll.paid',
            
            // System events
            'module.activated',
            'module.deactivated',
            'tenant.created',
            'tenant.updated',
        ];
    }
}