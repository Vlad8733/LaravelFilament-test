@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Import Products (CSV)</h1>

    @if(session('error'))
        <div class="mb-4 text-red-600">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    @if(session('failed_file'))
        <div class="mb-4">
            <a href="{{ session('failed_file') }}" class="text-blue-600 underline">Download failed rows CSV</a>
        </div>
    @endif

    <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">CSV file</label>
            <input type="file" name="csv_file" accept=".csv" required class="mt-1" />
            @error('csv_file')<div class="text-red-600">{{ $message }}</div>@enderror
        </div>

        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Upload and Import</button>
        </div>
    </form>
</div>
@endsection
