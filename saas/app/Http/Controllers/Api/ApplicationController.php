<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Application;
use BaoProd\Workforce\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Get all applications with filters
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Application::where('tenant_id', $tenant->id)
                           ->with(['job', 'candidate', 'reviewer']);

        // Apply filters based on user type
        if ($user->isCandidate()) {
            $query->where('candidate_id', $user->id);
        } elseif ($user->isEmployer()) {
            $query->whereHas('job', function ($q) use ($user) {
                $q->where('employer_id', $user->id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        $applications = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    /**
     * Get a specific application
     */
    public function show(Request $request, Application $application): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        if ($application->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        // Check access permissions
        if ($user->isCandidate() && $application->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this application'
            ], 403);
        }

        if ($user->isEmployer() && $application->job->employer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this application'
            ], 403);
        }

        $application->load(['job.employer', 'candidate', 'reviewer']);

        return response()->json([
            'success' => true,
            'data' => $application
        ]);
    }

    /**
     * Create a new application
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Only candidates can create applications
        if (!$user->isCandidate()) {
            return response()->json([
                'success' => false,
                'message' => 'Only candidates can apply for jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'cover_letter' => 'nullable|string',
            'documents' => 'nullable|array',
            'expected_salary' => 'nullable|numeric|min:0',
            'available_start_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $job = Job::findOrFail($request->job_id);

        // Check if job belongs to tenant
        if ($job->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // Check if job is active
        if (!$job->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This job is no longer accepting applications'
            ], 400);
        }

        // Check if user already applied
        $existingApplication = Application::where('job_id', $request->job_id)
                                         ->where('candidate_id', $user->id)
                                         ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job'
            ], 400);
        }

        $application = Application::create([
            'tenant_id' => $tenant->id,
            'job_id' => $request->job_id,
            'candidate_id' => $user->id,
            'cover_letter' => $request->cover_letter,
            'documents' => $request->documents,
            'expected_salary' => $request->expected_salary,
            'available_start_date' => $request->available_start_date,
            'status' => 'pending',
        ]);

        $application->load(['job', 'candidate']);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application
        ], 201);
    }

    /**
     * Update an application
     */
    public function update(Request $request, Application $application): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        if ($application->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        // Check access permissions
        if ($user->isCandidate() && $application->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this application'
            ], 403);
        }

        if ($user->isEmployer() && $application->job->employer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this application'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,reviewed,shortlisted,interviewed,accepted,rejected',
            'cover_letter' => 'nullable|string',
            'documents' => 'nullable|array',
            'expected_salary' => 'nullable|numeric|min:0',
            'available_start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'cover_letter', 'documents', 'expected_salary', 'available_start_date'
        ]);

        // Only employers/admins can change status and add notes
        if ($user->isEmployer() || $user->isAdmin()) {
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                $updateData['reviewed_at'] = now();
                $updateData['reviewed_by'] = $user->id;
            }
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }
        }

        $application->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully',
            'data' => $application->fresh()
        ]);
    }

    /**
     * Delete an application
     */
    public function destroy(Request $request, Application $application): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        if ($application->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        // Only candidate can delete their own application
        if ($application->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this application'
            ], 403);
        }

        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully'
        ]);
    }

    /**
     * Get application statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();
        
        $query = Application::where('tenant_id', $tenant->id);

        // Apply filters based on user type
        if ($user->isCandidate()) {
            $query->where('candidate_id', $user->id);
        } elseif ($user->isEmployer()) {
            $query->whereHas('job', function ($q) use ($user) {
                $q->where('employer_id', $user->id);
            });
        }

        $stats = [
            'total' => $query->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'reviewed' => $query->clone()->where('status', 'reviewed')->count(),
            'shortlisted' => $query->clone()->where('status', 'shortlisted')->count(),
            'interviewed' => $query->clone()->where('status', 'interviewed')->count(),
            'accepted' => $query->clone()->where('status', 'accepted')->count(),
            'rejected' => $query->clone()->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Bulk update applications status
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Only employers/admins can bulk update
        if (!$user->isEmployer() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to bulk update applications'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'exists:applications,id',
            'status' => 'required|in:pending,reviewed,shortlisted,interviewed,accepted,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $applications = Application::where('tenant_id', $tenant->id)
                                  ->whereIn('id', $request->application_ids)
                                  ->get();

        // Check if all applications belong to user's jobs (for employers)
        if ($user->isEmployer()) {
            $userJobIds = $user->postedJobs()->pluck('id')->toArray();
            $invalidApplications = $applications->filter(function ($app) use ($userJobIds) {
                return !in_array($app->job_id, $userJobIds);
            });

            if ($invalidApplications->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some applications do not belong to your jobs'
                ], 403);
            }
        }

        $updatedCount = 0;
        foreach ($applications as $application) {
            $application->update([
                'status' => $request->status,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'notes' => $request->notes,
            ]);
            $updatedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updatedCount} applications",
            'data' => ['updated_count' => $updatedCount]
        ]);
    }
}