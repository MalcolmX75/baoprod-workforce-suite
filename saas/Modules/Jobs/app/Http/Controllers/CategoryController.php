<?php

namespace Modules\Jobs\Http\Controllers;

use BaoProd\Workforce\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Jobs\Models\JobCategory;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = JobCategory::latest()->paginate(10);
        return view('jobs::categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('jobs::categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name',
        ]);

        JobCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'tenant_id' => auth()->user()->tenant_id, // Assuming tenant_id is on the user model
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(JobCategory $category)
    {
        return view('jobs::categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobCategory $category)
    {
        return view('jobs::categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobCategory $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
