@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Create Marketplace for: {{ basename($csvFile->filename) }}</h3>

    <div class="mb-3">
        <a href="{{ route('csv.index') }}" class="btn btn-secondary"> Back</a>
    </div>

    <form action="{{ route('csv.store-type') }}" method="POST">
        @csrf
        <input type="hidden" name="csv_file_id" value="{{ $csvFile->id }}">

        <div class="mb-3">
            <label class="form-label">Marketplace Name</label>
            <input type="text" name="type_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Columns to Include</label>
            <div class="row">
                @foreach ($headers as $header)
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $header }}" id="col-{{ $loop->index }}">
                            <label class="form-check-label" for="col-{{ $loop->index }}">{{ $header }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">Save Marketplace</button>
    </form>
</div>
@endsection
