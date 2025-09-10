@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Applications for {{ $job->title }}</h1>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2">Candidate</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Applied At</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $application)
                    <tr>
                        <td class="border px-4 py-2">{{ $application->candidate->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $application->status }}</td>
                        <td class="border px-4 py-2">{{ $application->applied_at->format('Y-m-d') }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('jobs.applications.show', $application) }}" class="text-blue-500">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
