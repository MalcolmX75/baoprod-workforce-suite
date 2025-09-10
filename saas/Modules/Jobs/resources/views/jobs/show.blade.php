@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">{{ $job->title }}</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>Category:</strong> {{ $job->category->name ?? 'N/A' }}</p>
            <p><strong>Employer:</strong> {{ $job->employer->name ?? 'N/A' }}</p>
            <p><strong>Location:</strong> {{ $job->location }}</p>
            <p><strong>Type:</strong> {{ $job->type }}</p>
            <p><strong>Status:</strong> {{ $job->status }}</p>
            <div class="mt-4">
                <h2 class="text-xl font-bold">Description</h2>
                <p>{{ $job->description }}</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('jobs.applications.index', $job) }}" class="bg-green-500 text-white px-4 py-2 rounded">View Applications</a>
            </div>
        </div>
    </div>
@endsection
