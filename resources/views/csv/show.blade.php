@extends('layouts.app')

@section('content')
    <h2>
        Editing CSV: {{ basename($csvFile->filename) }}
        @if (!empty($type))
            <small class="text-muted">({{ ucfirst($type) }} version)</small>
        @endif
    </h2>

    <div class="mb-3">
        <a href="{{ route('csv.index') }}" class="btn btn-primary">Back</a>
    </div>

    {{-- 🔍 Search Feature --}}
    <div class="d-flex justify-content-center mb-4">
        <div class="input-group w-auto">
            <select id="header-select" class="form-select form-select-sm" style="width: auto;">
                @foreach ($headers as $header)
                    <option value="{{ $header }}"
                        {{ in_array($header, json_decode($csvFile->subfile_columns, true)[$type] ?? []) ? 'selected' : '' }}>
                        {{ $header }}
                    </option>
                @endforeach
            </select>
            <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search..." style="min-width: 250px;">
        </div>
    </div>

    @if(auth()->user()->hasRole('admin'))
        <form method="POST" action="{{ route('csv.save') }}">
            @csrf
            <input type="hidden" name="file_id" value="{{ $csvFile->id }}">
            <input type="hidden" name="type" value="{{ $type ?? '' }}">

            <div class="mb-3">
                @if (empty($type))
                    <button type="button" class="btn btn-secondary" onclick="addColumn()">+ Add Column</button>
                    <button type="button" class="btn btn-secondary" onclick="addRow()">+ Add Row</button>
                @endif
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr id="header-row">
                        @foreach ($headers as $header)
                            <th><input type="text" name="headers[]" value="{{ $header }}"></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="csv-body">
                    @foreach ($paginatedData as $i => $row)
                        @php
                            $originalIndex = ($paginatedData->currentPage() - 1) * $paginatedData->perPage() + $i;
                        @endphp
                        <tr>
                            <input type="hidden" name="row_indexes[]" value="{{ $originalIndex }}">
                            @foreach ($headers as $j => $header)
                                <td>
                                    <input type="text" name="rows_flat[]" value="{{ $row[$j] ?? '' }}">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {{ $paginatedData->links() }}
            </div>

            <br>
            @if (empty($type))
                <button type="submit" class="btn btn-success">Save CSV</button>
            @endif
        </form>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="csv-body">
                @foreach ($paginatedData as $row)
                    <tr>
                        @foreach ($headers as $j => $header)
                            <td>{{ $row[$j] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $paginatedData->links() }}
        </div>
    @endif

    {{-- 🔧 JS for Column & Row Management --}}
    <script>
        function addColumn() {
            const headerRow = document.getElementById("header-row");
            const newTh = document.createElement("th");
            newTh.innerHTML = '<input type="text" name="headers[]" value="New Column">';
            headerRow.appendChild(newTh);

            const bodyRows = document.querySelectorAll("#csv-body tr");
            bodyRows.forEach(row => {
                const newTd = document.createElement("td");
                newTd.innerHTML = '<input type="text" name="rows_flat[]" value="">';
                row.appendChild(newTd);
            });
        }

        function addRow() {
            const tbody = document.getElementById('csv-body');
            const colCount = document.querySelectorAll("thead tr th").length;
            const rowCount = tbody.querySelectorAll("tr").length;
            const newRow = document.createElement('tr');

            // Add hidden input for row index = -1 (new row)
            newRow.innerHTML += `<input type="hidden" name="row_indexes[]" value="-1">`;

            for (let i = 0; i < colCount; i++) {
                const newCell = document.createElement('td');
                newCell.innerHTML = `<input type="text" name="rows_flat[]" value="">`;
                newRow.appendChild(newCell);
            }

            tbody.appendChild(newRow);
        }

        // 🔍 Search Feature
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const headerSelect = document.getElementById('header-select');
            const rows = document.querySelectorAll('#csv-body tr');

            searchInput.addEventListener('input', function () {
                const selectedHeader = headerSelect.value;
                const query = searchInput.value.toLowerCase();

                const headers = Array.from(document.querySelectorAll("thead tr th input")).map(input => input.value);
                const selectedIndex = headers.indexOf(selectedHeader);

                if (selectedIndex === -1) return;

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const targetCell = cells[selectedIndex];

                    let text = '';
                    if (targetCell?.querySelector('input')) {
                        text = targetCell.querySelector('input').value.toLowerCase();
                    } else if (targetCell) {
                        text = targetCell.textContent.toLowerCase();
                    }

                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
