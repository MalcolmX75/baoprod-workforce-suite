<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    /**
     * Get all jobs with filters
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        
        $query = Job::where('tenant_id', $tenant->id)
                   ->with(['employer', 'applications']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('location')) {
            $query->inLocation($request->location);
        }

        if ($request->has('remote')) {
            $query->remote();
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('salary_min') || $request->has('salary_max')) {
            $query->withSalaryRange($request->salary_min, $request->salary_max);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        // Default to active jobs for public access
        if (!$request->has('status')) {
            $query->active();
        }

        $jobs = $query->orderBy('created_at', 'desc')
                     ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    /**
     * Get a specific job
     */
    public function show(Job $job): JsonResponse
    {
        $tenant = app('tenant');
        
        if ($job->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        $job->load(['employer', 'applications.candidate']);

        return response()->json([
            'success' => true,
            'data' => $job
        ]);
    }

    /**
     * Create a new job
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Only employers can create jobs
        if (!$user->isEmployer() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only employers can create jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'type' => 'required|in:full_time,part_time,contract,temporary',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_currency' => 'required|string|size:3',
            'salary_period' => 'required|in:monthly,hourly,daily',
            'start_date' => 'nullable|date|after:today',
            'end_date' => 'nullable|date|after:start_date',
            'positions_available' => 'required|integer|min:1',
            'benefits' => 'nullable|array',
            'skills_required' => 'nullable|array',
            'experience_required' => 'required|integer|min:0',
            'education_level' => 'nullable|string',
            'is_remote' => 'boolean',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $job = Job::create([
            'tenant_id' => $tenant->id,
            'employer_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'type' => $request->type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'salary_currency' => $request->salary_currency,
            'salary_period' => $request->salary_period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'positions_available' => $request->positions_available,
            'benefits' => $request->benefits,
            'skills_required' => $request->skills_required,
            'experience_required' => $request->experience_required,
            'education_level' => $request->education_level,
            'is_remote' => $request->boolean('is_remote'),
            'is_featured' => $request->boolean('is_featured'),
            'expires_at' => $request->expires_at,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'data' => $job
        ], 201);
    }

    /**
     * Update a job
     */
    public function update(Request $request, Job $job): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        if ($job->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // Only job owner or admin can update
        if ($job->employer_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this job'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'requirements' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'type' => 'sometimes|in:full_time,part_time,contract,temporary',
            'status' => 'sometimes|in:draft,published,closed,filled',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'salary_currency' => 'sometimes|string|size:3',
            'salary_period' => 'sometimes|in:monthly,hourly,daily',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'positions_available' => 'sometimes|integer|min:1',
            'benefits' => 'nullable|array',
            'skills_required' => 'nullable|array',
            'experience_required' => 'sometimes|integer|min:0',
            'education_level' => 'nullable|string',
            'is_remote' => 'boolean',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'title', 'description', 'requirements', 'location', 'latitude', 'longitude',
            'type', 'status', 'salary_min', 'salary_max', 'salary_currency', 'salary_period',
            'start_date', 'end_date', 'positions_available', 'benefits', 'skills_required',
            'experience_required', 'education_level', 'expires_at'
        ]);

        // Handle boolean fields
        if ($request->has('is_remote')) {
            $updateData['is_remote'] = $request->boolean('is_remote');
        }
        if ($request->has('is_featured')) {
            $updateData['is_featured'] = $request->boolean('is_featured');
        }

        // Set published_at when status changes to published
        if ($request->has('status') && $request->status === 'published' && $job->status !== 'published') {
            $updateData['published_at'] = now();
        }

        $job->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job->fresh()
        ]);
    }

    /**
     * Delete a job
     */
    public function destroy(Job $job): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        if ($job->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // Only job owner or admin can delete
        if ($job->employer_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this job'
            ], 403);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully'
        ]);
    }

    /**
     * Get job statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        $query = Job::where('tenant_id', $tenant->id);

        // If user is employer, only show their jobs
        if ($user->isEmployer()) {
            $query->where('employer_id', $user->id);
        }

        $stats = [
            'total' => $query->count(),
            'published' => $query->clone()->where('status', 'published')->count(),
            'draft' => $query->clone()->where('status', 'draft')->count(),
            'closed' => $query->clone()->where('status', 'closed')->count(),
            'filled' => $query->clone()->where('status', 'filled')->count(),
            'featured' => $query->clone()->where('is_featured', true)->count(),
            'remote' => $query->clone()->where('is_remote', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}