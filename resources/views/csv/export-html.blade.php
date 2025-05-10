<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ basename($csvFile->filename) }}</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>CSV: {{ basename($csvFile->filename) }}</h2>
    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
