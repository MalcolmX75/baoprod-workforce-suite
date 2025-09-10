@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Application from {{ $application->candidate->name ?? 'N/A' }}</h1>
        <div class="bg-white p-4 rounded shadow">
            <p><strong>Job:</strong> {{ $application->job->title ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $application->status }}</p>
            <p><strong>Applied At:</strong> {{ $application->applied_at->format('Y-m-d H:i') }}</p>
            <div class="mt-4">
                <h2 class="text-xl font-bold">Cover Letter</h2>
                <p>{{ $application->cover_letter }}</p>
            </div>
            <div class="mt-4">
                <h2 class="text-xl font-bold">Update Status</h2>
                <form action="{{ route('jobs.applications.update', $application) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <select name="status" class="border-gray-300 rounded">
                        <option value="pending" @if($application->status == 'pending') selected @endif>Pending</option>
                        <option value="reviewed" @if($application->status == 'reviewed') selected @endif>Reviewed</option>
                        <option value="shortlisted" @if($application->status == 'shortlisted') selected @endif>Shortlisted</option>
                        <option value="rejected" @if($application->status == 'rejected') selected @endif>Rejected</option>
                        <option value="hired" @if($application->status == 'hired') selected @endif>Hired</option>
                    </select>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection
