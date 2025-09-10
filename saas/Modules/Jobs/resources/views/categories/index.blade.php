@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Job Categories</h1>
        <a href="{{ route('jobs.categories.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Create Category</a>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2">Name</th>
                    <th class="py-2">Slug</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td class="border px-4 py-2">{{ $category->name }}</td>
                        <td class="border px-4 py-2">{{ $category->slug }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('jobs.categories.edit', $category) }}" class="text-yellow-500">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
