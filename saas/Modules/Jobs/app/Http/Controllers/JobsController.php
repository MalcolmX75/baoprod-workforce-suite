<?php

namespace Modules\Jobs\Http\Controllers;

use BaoProd\Workforce\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Jobs\Models\Job;
use Modules\Jobs\Models\JobCategory;

class JobsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = Job::with('category', 'employer')->latest()->paginate(10);
        return view('jobs::jobs.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = JobCategory::all();
        return view('jobs::jobs.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'job_category_id' => 'required|exists:job_categories,id',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        $job = Job::create($request->all() + [
            'employer_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id, // Assuming tenant_id is on the user model
        ]);

        return redirect()->route('jobs.show', $job)->with('success', 'Job created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Job $job)
    {
        return view('jobs::jobs.show', compact('job'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job $job)
    {
        $categories = JobCategory::all();
        return view('jobs::jobs.edit', compact('job', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'job_category_id' => 'required|exists:job_categories,id',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        $job->update($request->all());

        return redirect()->route('jobs.show', $job)->with('success', 'Job updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return redirect()->route('jobs.index')->with('success', 'Job deleted successfully.');
    }
}
