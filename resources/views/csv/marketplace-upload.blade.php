@extends('layouts.app') <!-- assuming you have a layout -->

@section('content')
    <div class="container">
        <h1>Upload Marketplace CSV</h1>
        <form action="{{ route('csv.storeMarketplaceCsv') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="csv_file">Select CSV File:</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Upload Marketplace CSV</button>
        </form>
    </div>
@endsection
