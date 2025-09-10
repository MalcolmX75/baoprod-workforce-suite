<?php

namespace Modules\Jobs\Http\Controllers;

use BaoProd\Workforce\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Jobs\Models\JobApplication;
use Modules\Jobs\Models\Job;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource for a specific job.
     */
    public function index(Job $job)
    {
        // Add authorization check if needed, e.g., Gate::authorize('view-applications', $job);
        $applications = $job->applications()->with('candidate')->latest()->paginate(10);
        return view('jobs::applications.index', compact('job', 'applications'));
    }

    /**
     * Show the specified resource.
     */
    public function show(JobApplication $application)
    {
        // Add authorization check
        $application->load('job.employer', 'candidate');
        return view('jobs::applications.show', compact('application'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobApplication $application)
    {
        // Add authorization check
        $request->validate([
            'status' => 'required|in:pending,reviewed,shortlisted,rejected,hired',
        ]);

        $application->update($request->only('status'));

        return redirect()->route('applications.show', $application)->with('success', 'Application status updated successfully.');
    }
}
