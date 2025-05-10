@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Marketplaces for CSV: {{ basename($csvFile->filename) }}</h2>

        <div class="mb-3">
        <a href="{{ route('csv.index') }}" class="btn btn-primary">Back</a>
    </div>


        @if ($marketplaces->isEmpty())
            <p>No marketplaces defined.</p>
        @else
            <ul class="list-group">
                @foreach ($marketplaces as $marketplace)
                    @php
                        $type = strtolower($marketplace->type_name);
                        $fileName = pathinfo($csvFile->filename, PATHINFO_FILENAME) . '_' . $type . '.csv';
                        $filePath = asset('storage/csvs/' . $fileName);
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>{{ ucfirst($type) }}</strong>
                        <span>
                            <a href="{{ route('csv.show', ['id' => $csvFile->id, 'type' => $type]) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ $filePath }}" download class="btn btn-sm btn-secondary">Download</a>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
