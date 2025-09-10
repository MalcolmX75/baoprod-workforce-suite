<?php

namespace BaoProd\Workforce\Http\Controllers\Api\Public;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Application;
use BaoProd\Workforce\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class JobBoardSyncController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Get published jobs for external consumption (WordPress, etc.)
     * GET /api/public/jobs
     */
    public function getJobs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'category' => 'string',
            'location' => 'string',
            'type' => 'string',
            'featured_only' => 'boolean',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Job::with(['employer:id,first_name,last_name,email'])
                    ->where('tenant_id', $request->tenant_id)
                    ->active()
                    ->published()
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('published_at', 'desc');

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('featured_only')) {
            $query->where('is_featured', true);
        }

        $perPage = $request->get('per_page', 20);
        $jobs = $query->paginate($perPage);

        $data = $jobs->items();
        foreach ($data as $job) {
            // Add computed fields
            $job->formatted_salary = $job->salary_range;
            $job->days_since_published = $job->published_at ? $job->published_at->diffInDays() : null;
            $job->employer_name = $job->employer ? $job->employer->full_name : null;
            
            // Remove sensitive data
            unset($job->employer);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'last_page' => $jobs->lastPage(),
            ],
        ]);
    }

    /**
     * Get single job details for external consumption
     * GET /api/public/jobs/{id}
     */
    public function getJob(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $job = Job::with(['employer:id,first_name,last_name'])
                  ->where('id', $id)
                  ->where('tenant_id', $request->tenant_id)
                  ->active()
                  ->published()
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or not available',
            ], 404);
        }

        $job->formatted_salary = $job->salary_range;
        $job->employer_name = $job->employer ? $job->employer->full_name : null;
        $job->applications_count = $job->applications()->count();
        
        // Remove sensitive data
        unset($job->employer);

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Submit application from external source (WordPress, etc.)
     * POST /api/public/jobs/{jobId}/apply
     */
    public function submitApplication(Request $request, $jobId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string|max:2000',
            'cv_url' => 'nullable|url',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'expected_salary' => 'nullable|numeric|min:0',
            'available_start_date' => 'nullable|date|after:today',
            'source' => 'string|max:50', // wordpress, website, etc.
            'source_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if job exists and is active
        $job = Job::where('id', $jobId)
                  ->where('tenant_id', $request->tenant_id)
                  ->active()
                  ->published()
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or no longer accepting applications',
            ], 404);
        }

        try {
            // Check for duplicate application
            $existingApplication = Application::where('job_id', $jobId)
                                            ->where('tenant_id', $request->tenant_id)
                                            ->whereRaw("JSON_EXTRACT(candidate_data, '$.email') = ?", [$request->email])
                                            ->first();

            if ($existingApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already applied to this position',
                ], 409);
            }

            // Handle file upload if provided
            $cvUrl = $request->cv_url;
            if ($request->hasFile('cv_file')) {
                $cvUrl = $request->file('cv_file')->store('cvs', 'public');
                $cvUrl = asset('storage/' . $cvUrl);
            }

            // Create application record
            $application = Application::create([
                'tenant_id' => $request->tenant_id,
                'job_id' => $jobId,
                'candidate_id' => null, // External application
                'status' => 'pending',
                'cover_letter' => $request->cover_letter,
                'expected_salary' => $request->expected_salary,
                'available_start_date' => $request->available_start_date,
                'candidate_data' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'cv_url' => $cvUrl,
                    'source' => $request->get('source', 'external'),
                    'source_url' => $request->source_url,
                    'applied_at' => now()->toISOString(),
                ],
            ]);

            // Trigger webhook for internal systems
            $this->webhookService->trigger('application.created', [
                'application_id' => $application->id,
                'job_id' => $jobId,
                'tenant_id' => $request->tenant_id,
                'candidate_email' => $request->email,
                'source' => 'external_api',
            ]);

            Log::info('External application submitted', [
                'application_id' => $application->id,
                'job_id' => $jobId,
                'email' => $request->email,
                'source' => $request->get('source', 'external'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => [
                    'application_id' => $application->id,
                    'job_title' => $job->title,
                    'status' => $application->status,
                    'submitted_at' => $application->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error submitting external application', [
                'job_id' => $jobId,
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your application. Please try again.',
            ], 500);
        }
    }

    /**
     * Webhook endpoint for external systems to push data
     * POST /api/public/webhook/jobs
     */
    public function receiveJobWebhook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'action' => 'required|in:create,update,delete,sync',
            'data' => 'required|array',
            'external_id' => 'required|string',
            'source' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $action = $request->action;
            $data = $request->data;
            $externalId = $request->external_id;
            $source = $request->source;

            switch ($action) {
                case 'create':
                case 'update':
                    $this->syncJobFromExternal($request->tenant_id, $externalId, $source, $data, $action === 'create');
                    break;
                
                case 'delete':
                    $this->deleteExternalJob($request->tenant_id, $externalId, $source);
                    break;
                
                case 'sync':
                    $this->fullSyncFromExternal($request->tenant_id, $source, $data);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing job webhook', [
                'tenant_id' => $request->tenant_id,
                'action' => $request->action,
                'external_id' => $request->external_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
            ], 500);
        }
    }

    /**
     * Sync job from external system
     */
    private function syncJobFromExternal(int $tenantId, string $externalId, string $source, array $data, bool $isNew): void
    {
        $jobData = [
            'tenant_id' => $tenantId,
            'employer_id' => $data['employer_id'] ?? 1, // Default employer
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'requirements' => $data['requirements'] ?? '',
            'location' => $data['location'] ?? '',
            'type' => $data['type'] ?? 'full_time',
            'category' => $data['category'] ?? null,
            'status' => $data['status'] ?? 'published',
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'salary_currency' => $data['salary_currency'] ?? 'XOF',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_remote' => $data['is_remote'] ?? false,
            'published_at' => $data['published_at'] ?? now(),
            'external_data' => [
                'source' => $source,
                'external_id' => $externalId,
                'synced_at' => now()->toISOString(),
                'original_data' => $data,
            ],
        ];

        Job::updateOrCreate([
            'tenant_id' => $tenantId,
            'external_id' => $externalId,
            'external_source' => $source,
        ], $jobData);
    }

    /**
     * Delete job from external sync
     */
    private function deleteExternalJob(int $tenantId, string $externalId, string $source): void
    {
        Job::where('tenant_id', $tenantId)
           ->where('external_id', $externalId)
           ->where('external_source', $source)
           ->delete();
    }

    /**
     * Full sync from external system
     */
    private function fullSyncFromExternal(int $tenantId, string $source, array $jobs): void
    {
        foreach ($jobs as $jobData) {
            if (isset($jobData['external_id'])) {
                $this->syncJobFromExternal($tenantId, $jobData['external_id'], $source, $jobData, false);
            }
        }
    }
}