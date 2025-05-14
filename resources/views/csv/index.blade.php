@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <div class="list-group-item active text-center">Master Listing</div>

                @forelse($csvFiles as $file)
                    @php
                        $subfiles = json_decode($file->subfile_columns, true) ?? [];
                    @endphp

                    <div class="list-group-item">
                        {{-- Main View --}}
                        <a href="{{ route('csv.show', ['id' => $file->id]) }}" class="fw-bold d-block text-decoration-none">
                            {{ basename($file->filename) }}
                        </a>

                       {{-- Marketplace --}}
                       @if (!empty($subfiles))
                            <div class="ms-3 mt-1">
                                <div class="text-start">
                                            Marketplace
                                </div>
                                @foreach ($subfiles as $subfileType => $columns)
    <div class="d-flex justify-content-between align-items-center mb-1">
        <a href="{{ route('csv.show', ['id' => $file->id, 'type' => $subfileType]) }}" class="text-decoration-none">
            ▸ {{ ucfirst($subfileType) }} View
        </a>

        @if(auth()->user()->hasRole('admin'))
            <div class="btn-group ms-2">
                {{-- Edit Button --}}
                <a href="{{ route('csv.edit-type', ['id' => $file->id, 'type' => $subfileType]) }}"
                   class="btn btn-sm btn-outline-primary"
                   title="Edit {{ ucfirst($subfileType) }}">
                    ✏️
                </a>

                {{-- Delete Button --}}
                <form action="{{ route('csv.destroy-subfile', ['id' => $file->id, 'type' => $subfileType]) }}"
                      method="POST"
                      onsubmit="return confirm('Delete the {{ $subfileType }} subfile?');"
                      style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete {{ ucfirst($subfileType) }}">
                        🗑️
                    </button>
                </form>
            </div>
        @endif
    </div>
@endforeach

                                <!-- @foreach ($subfiles as $subfileType => $columns)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <a href="{{ route('csv.show', ['id' => $file->id, 'type' => $subfileType]) }}" class="text-decoration-none">
                                            ▸ {{ ucfirst($subfileType) }} View
                                        </a>

                                        @if(auth()->user()->hasRole('admin'))
                                            <form action="{{ route('csv.destroy-subfile', ['id' => $file->id, 'type' => $subfileType]) }}" method="POST" onsubmit="return confirm('Delete the {{ $subfileType }} subfile?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger ms-2" style="padding: 0.1rem 0.4rem;" title="Delete {{ ucfirst($subfileType) }}">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach -->

                                <!-- @foreach ($subfiles as $subfileType => $columns)
                                    <a href="{{ route('csv.show', ['id' => $file->id, 'type' => $subfileType]) }}" class="d-block text-decoration-none mb-1">
                                        ▸ {{ ucfirst($subfileType) }} View
                                    </a>
                                @endforeach -->
                            </div>
                        @endif



<!-- @if (!empty($subfiles))
    <div class="dropdown ms-3 mt-1">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle w-100 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Marketplace
        </button>
        <ul class="dropdown-menu">
            @foreach ($subfiles as $subfileType => $columns)
                <li>
                    <a class="dropdown-item" href="{{ route('csv.show', ['id' => $file->id, 'type' => $subfileType]) }}">
                        {{ ucfirst($subfileType) }} View
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif -->

{{-- Create Marketplace --}}
<div class="mt-2 text-center">
    <a href="{{ route('csv.create-type', $file->id) }}" class="btn btn-sm btn-success w-100">
        + Create Marketplace
    </a>
</div>
{{-- View All Marketplaces --}}
<div class="mt-1 text-center">
    <a href="{{ route('csv.marketplaces', $file->id) }}" class="btn btn-sm btn-info w-100 text-white">
        View All Marketplaces
    </a>
</div>



                        {{-- Export Full --}}
                        <div class="dropdown mt-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" data-bs-toggle="dropdown" type="button">
                                Export Full
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'csv']) }}">CSV</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'pdf']) }}">PDF</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'txt']) }}">TXT</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'json']) }}">JSON</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'xml']) }}">XML</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'html']) }}">HTML (View)</a></li>
                                <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'html-download']) }}">HTML (Download)</a></li>
                            </ul>
                        </div>

                        {{-- Export Sub-files --}}
                        @if (!empty($subfiles))
                            <div class="dropdown mt-1">
                                <button class="btn btn-sm btn-outline-dark dropdown-toggle w-100" data-bs-toggle="dropdown" type="button">
                                    Export Sub-files
                                </button>
                                <ul class="dropdown-menu w-100">
                                    @foreach ($subfiles as $subfileType => $columns)
                                        <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'csv']) . '?type=' . $subfileType }}">{{ ucfirst($subfileType) }} CSV</a></li>
                                        <li><a class="dropdown-item" href="{{ route('csv.export', [$file->id, 'pdf']) . '?type=' . $subfileType }}">{{ ucfirst($subfileType) }} PDF</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Delete Button --}}
                        @if(auth()->user()->hasRole('admin'))
                            <form action="{{ route('csv.destroy', $file->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to delete this file and its subfiles?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="list-group-item text-muted">No CSV files found.</div>
                @endforelse
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-md-9">
            <h2 class="mb-4">CSV File Manager</h2>

            {{-- Upload Form (Admins Only) --}}
            @if(auth()->user()->hasRole('admin'))
                <div class="card mb-4">
                    <div class="card-header"><h5>Upload New CSV File</h5></div>
                    <div class="card-body">
                        <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Choose CSV File</label>
                                <input type="file" name="csv_file" class="form-control" required accept=".csv,.txt">
                            </div>
                            <button type="submit" class="btn btn-primary">Upload CSV</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection