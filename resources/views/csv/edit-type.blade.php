@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Columns for: {{ ucfirst($csvType->type_name) }}</h3>
    <p>File: {{ basename($csvFile->filename) }}</p>
    <div class="mb-3">
        <a href="{{ route('csv.index') }}" class="btn btn-secondary"> Back</a>
    </div>

    <form method="POST" action="{{ route('csv.save-type-columns') }}">
        @csrf
        <input type="hidden" name="file_id" value="{{ $csvFile->id }}">
        <input type="hidden" name="type" value="{{ $csvType->type_name }}">

        <div class="mb-3">
            <label>Select Columns to Include</label>
            <div class="row">
                @foreach ($headers as $header)
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $header }}"
                                   id="col-{{ $loop->index }}"
                                   {{ in_array($header, $selectedColumns ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="col-{{ $loop->index }}">{{ $header }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Update Subfile Columns</button>
        <a href="{{ route('csv.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
