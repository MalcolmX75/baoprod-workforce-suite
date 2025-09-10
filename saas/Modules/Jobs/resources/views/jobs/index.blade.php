@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Jobs</h1>
        <a href="{{ route('jobs.posts.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Create Job</a>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2">Title</th>
                    <th class="py-2">Category</th>
                    <th class="py-2">Employer</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                    <tr>
                        <td class="border px-4 py-2">{{ $job->title }}</td>
                        <td class="border px-4 py-2">{{ $job->category->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $job->employer->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $job->status }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('jobs.posts.show', $job) }}" class="text-blue-500">View</a>
                            <a href="{{ route('jobs.posts.edit', $job) }}" class="text-yellow-500 ml-2">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
