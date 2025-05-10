@extends('layouts.app')

@section('content')
    <h2>Create Marketplace for: {{basename($csvFile->filename)}}</h2>
    <div class="mb-3">
        <a href="{{ route('csv.index') }}" class="btn btn-primary">Back</a>
    </div>


    <form action="{{ route('csv.store-type') }}" method="POST">
        @csrf
        <input type="hidden" name="csv_file_id" value="{{ $csvFile->id }}">
        
        <div class="mb-3">
            <label>Marketplace Name</label>
            <input type="text" name="type_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Select Columns</label><br>
            @foreach ($headers as $header)
                <div>
                    <input type="checkbox" name="columns[]" value="{{ $header }}"> {{ $header }}
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Save Marketplace</button>
    </form>
@endsection
