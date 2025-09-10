@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Edit Job</h1>
        <form action="{{ route('jobs.posts.update', $job) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Title</label>
                <input type="text" name="title" id="title" class="w-full border-gray-300 rounded" value="{{ $job->title }}">
            </div>
            <div class="mb-4">
                <label for="job_category_id" class="block text-gray-700">Category</label>
                <select name="job_category_id" id="job_category_id" class="w-full border-gray-300 rounded">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @if($job->job_category_id == $category->id) selected @endif>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700">Description</label>
                <textarea name="description" id="description" rows="5" class="w-full border-gray-300 rounded">{{ $job->description }}</textarea>
            </div>
            <div class="mb-4">
                <label for="location" class="block text-gray-700">Location</label>
                <input type="text" name="location" id="location" class="w-full border-gray-300 rounded" value="{{ $job->location }}">
            </div>
            <div class="mb-4">
                <label for="type" class="block text-gray-700">Type</label>
                <input type="text" name="type" id="type" class="w-full border-gray-300 rounded" value="{{ $job->type }}">
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded">
                    <option value="draft" @if($job->status == 'draft') selected @endif>Draft</option>
                    <option value="published" @if($job->status == 'published') selected @endif>Published</option>
                    <option value="archived" @if($job->status == 'archived') selected @endif>Archived</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
@endsection
