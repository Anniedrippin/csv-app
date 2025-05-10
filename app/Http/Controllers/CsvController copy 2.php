<?php

namespace App\Http\Controllers;

use App\Models\CsvType;
use App\Models\CsvFile;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Support\Collection;

class CsvController extends Controller
{
//     public function __construct()
// {
//     self::$AMAZON_COLUMNS = config('amazon_columns');
// }

    // private const AMAZON_COLUMNS = ['Status', 'Title', 'Parent SKU', 'Age Range Description'];
    // protected static array $AMAZON_COLUMNS = [];
    // private const AMAZON_COLUMNS = ['Customer Id', 'First Name', 'Company', 'Phone 1','Email'];

    // //private const EBAY_COLUMNS = ['element', 'quality', 'ebay_price','shirts'];
    // private const EBAY_COLUMNS = ['Website', 'quality', 'ebay_price','shirts'];
    // private const DOBA_COLUMNS = ['NTC','SPU Code','Product Name', 'Brand', 'SKU Code', 'Product Details'];

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            $csvFiles = CsvFile::all();
        } else {
            $csvFiles = CsvFile::where('user_id', $user->id)->get();
        }

        return view('csv.index', compact('csvFiles'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $originalName = pathinfo($request->file('csv_file')->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $request->file('csv_file')->getClientOriginalExtension();
        $safeOriginalName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
        $fileName = $safeOriginalName . '.' . $extension;

        $path = $request->file('csv_file')->storeAs('csvs', $fileName, 'public');
        Log::info('File stored at: ' . $path);

        CsvFile::create([
            'filename' => 'storage/csvs/' . $fileName,
            'user_id' => auth()->id(),
        ]);

        $fullPath = storage_path('app/public/csvs/' . $fileName);
        $this->generateSubFiles($fullPath, pathinfo($fileName, PATHINFO_FILENAME));

        return redirect()->route('csv.index')->with('success', 'CSV uploaded and processed.');
    }

    public function show($id, Request $request)
    {
        $type = $request->input('type');
        $csvFile = CsvFile::findOrFail($id);
        $baseFile = basename($csvFile->filename);
        $baseName = pathinfo($baseFile, PATHINFO_FILENAME);

        $targetFile = $type
            ? storage_path("app/public/csvs/{$baseName}_$type.csv")
            : storage_path("app/public/csvs/" . $baseFile);

        if (!file_exists($targetFile)) {
            abort(404, 'File not found.');
        }

        $data = array_map('str_getcsv', file($targetFile));
        $headers = array_shift($data);

        $page = $request->input('page', 1);
        $perPage = 10;
        $collection = collect($data);
        $paginatedData = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $fileUrl = asset("storage/csvs/" . basename($targetFile));

        return view('csv.show', compact('csvFile', 'headers', 'paginatedData', 'fileUrl', 'type'));
    }

    public function save(Request $request)
{
    $headers = $request->input('headers');
    $flatRows = $request->input('rows_flat');
    $rowIndexes = $request->input('row_indexes');
    $fileId = $request->input('file_id');

    $csvFile = CsvFile::findOrFail($fileId);
    $baseName = pathinfo($csvFile->filename, PATHINFO_FILENAME);
    $mainPath = storage_path('app/public/csvs/' . basename($csvFile->filename));

    if (!file_exists($mainPath)) {
        abort(404, 'Main file not found.');
    }

    $existingData = array_map('str_getcsv', file($mainPath));
    $existingHeaders = array_shift($existingData);

    // map new headers to old headers
    $headerMap = [];
    foreach ($headers as $i => $header) {
        $oldIndex = array_search($header, $existingHeaders);
        if ($oldIndex !== false) {
            $headerMap[$i] = $oldIndex;
        } else {
            $headerMap[$i] = count($existingHeaders); // new column
            $existingHeaders[] = $header;
        }
    }

    // Rebuild the updated dataset
    $updatedData = $existingData;
    $rowSize = count($headers);
    $rowChunks = array_chunk($flatRows, $rowSize);

    foreach ($rowChunks as $k => $newRow) {
        $rowIndex = (int) ($rowIndexes[$k] ?? -1);

        $updatedRow = array_fill(0, count($existingHeaders), '');

        foreach ($newRow as $colIndex => $value) {
            $mappedIndex = $headerMap[$colIndex] ?? $colIndex;
            $updatedRow[$mappedIndex] = $value;
        }

        if ($rowIndex >= 0 && isset($updatedData[$rowIndex])) {
            $updatedData[$rowIndex] = $updatedRow;
        } else {
            $updatedData[] = $updatedRow; // New row
        }
    }

    // Write back to file
    $handle = fopen($mainPath, 'w');
    fputcsv($handle, $existingHeaders);
    foreach ($updatedData as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);

    $this->generateSubFiles($mainPath, $baseName);

    return redirect()->route('csv.index')->with('success', 'CSV updated and synced successfully.');

}


 private function generateSubFiles(string $fullPath, string $baseName): void
{
    // Check if the CSV file exists at the given path
    if (!file_exists($fullPath)) {
        Log::error("CSV file not found at path: $fullPath");
        return;
    }

    // Read the CSV file and process it
    $data = array_map('str_getcsv', file($fullPath));
    $headers = array_shift($data);

    // Find the CsvFile record by its filename
    $csvFile = CsvFile::where('filename', 'storage/csvs/' . $baseName . '.csv')->first();
    if (!$csvFile) {
        Log::warning("No CsvFile DB record found for filename: storage/csvs/{$baseName}.csv");
        return;
    }

    // Fetch the related CsvTypes for the current CSV file
    $csvTypes = CsvType::where('csv_file_id', $csvFile->id)->get();
    $subfiles = [];

    // Loop through each CSV type to create subfiles
    foreach ($csvTypes as $type) {
        // Check if columns are already an array, otherwise decode JSON string
        $columns = is_array($type->columns) ? $type->columns : json_decode($type->columns, true);

        // If columns are invalid, log the error and skip to the next type
        if (!is_array($columns)) {
            Log::error("Invalid columns format for CsvType ID {$type->id}");
            continue;
        }

        // Sanitize the type name to make it valid for the filename
        $sanitizedTypeName = strtolower(preg_replace('/[^A-Za-z0-9_\-]/', '_', $type->type_name));
        $filename = "{$baseName}_{$sanitizedTypeName}.csv";

        // Log the generation of the subfile
        Log::info("Generating subfile: $filename with columns: " . implode(', ', $columns));

        // Generate the subfile with the selected columns
        if ($this->writeSubCsv($headers, $data, $columns, $filename)) {
            $subfiles[$sanitizedTypeName] = array_values(array_intersect($headers, $columns));
        } else {
            Log::warning("Subfile generation failed for type: {$type->type_name}");
        }
    }

    // Save the generated subfiles' columns in the CsvFile record
    $csvFile->subfile_columns = json_encode($subfiles);
    $csvFile->save();
}

    private function writeSubCsv(array $headers, array $data, array $targetCols, string $filename): bool
{
    $indexes = array_keys(array_intersect($headers, $targetCols));

    if (empty($indexes)) {
        \Log::warning("No matching columns found in headers for: " . implode(', ', $targetCols));
        return false;
    }

    $filteredHeaders = array_intersect_key($headers, array_flip($indexes));
    $filteredData = [];

    foreach ($data as $row) {
        $filteredData[] = array_intersect_key($row, array_flip($indexes));
    }

    $filePath = storage_path("app/public/csvs/" . $filename);
    \Log::info("Writing subfile to $filePath");

    $handle = fopen($filePath, 'w');
    fputcsv($handle, $filteredHeaders);
    foreach ($filteredData as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);

    return true;
}


    public function destroy($id)
{
    $csvFile = CsvFile::findOrFail($id);
    $baseName = pathinfo($csvFile->filename, PATHINFO_FILENAME);

    // Delete main file
    $mainPath = storage_path('app/public/csvs/' . basename($csvFile->filename));
    if (file_exists($mainPath)) {
        unlink($mainPath);
    }

    // Delete Amazon and eBay subfiles
    $amazonPath = storage_path("app/public/csvs/{$baseName}_amazon.csv");
    $ebayPath = storage_path("app/public/csvs/{$baseName}_ebay.csv");
    $dobaPath = storage_path("app/public/csvs/{$baseName}_doba.csv");

    if (file_exists($amazonPath)) {
        unlink($amazonPath);
    }
    if (file_exists($ebayPath)) {
        unlink($ebayPath);
    }
    if (file_exists($dobaPath)) {
        unlink($dobaPath);
    }

    // Delete DB record
    $csvFile->delete();

    return redirect()->route('csv.index')->with('success', 'CSV file and its subfiles deleted successfully.');
}


    public function export($id, $format, Request $request)
    {
        $type = $request->input('type');
        $csvFile = CsvFile::findOrFail($id);
        $baseName = pathinfo($csvFile->filename, PATHINFO_FILENAME);

        $targetFile = $type
            ? storage_path("app/public/csvs/{$baseName}_$type.csv")
            : storage_path("app/public/csvs/" . basename($csvFile->filename));

        if (!file_exists($targetFile)) {
            abort(404, 'File not found.');
        }

        $data = array_map('str_getcsv', file($targetFile));
        $headers = array_shift($data);

        switch ($format) {
            case 'csv':
                return response()->download($targetFile);

            case 'txt':
                $txt = implode("\t", $headers) . "\n";
                foreach ($data as $row) {
                    $txt .= implode("\t", $row) . "\n";
                }
                return response($txt)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="export.txt"');

            case 'json':
                $jsonArray = [];
                foreach ($data as $row) {
                    $jsonArray[] = array_combine($headers, $row);
                }
                return response()->json($jsonArray);

            case 'xml':
                $xml = new \SimpleXMLElement('<csv/>');
                foreach ($data as $row) {
                    $item = $xml->addChild('row');
                    foreach ($headers as $i => $header) {
                        $item->addChild($header, htmlspecialchars($row[$i] ?? ''));
                    }
                }
                return response($xml->asXML(), 200)
                    ->header('Content-Type', 'application/xml')
                    ->header('Content-Disposition', 'attachment; filename="export.xml"');

            case 'html':
                return view('csv.export-html', compact('headers', 'data', 'csvFile'));

            case 'html-download':
                $html = view('csv.export-html', compact('headers', 'data', 'csvFile'))->render();
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="export.html"');

            case 'pdf':
                $pdf = PDF::loadView('csv.export-html', compact('headers', 'data', 'csvFile'));
                return $pdf->download('export.pdf');

            default:
                abort(400, 'Invalid export format.');
        }
    }

    public function createTypeForm($csvFileId)
{
    // dd($csvFileId);
    $csvFile = CsvFile::findOrFail($csvFileId);
    //dd($csvFile);
    $path = storage_path('app/public/csvs/' . basename($csvFile->filename));


    if (!file_exists($path)) {
        abort(404, 'CSV not found.');
    }

    $data = array_map('str_getcsv', file($path));
    $headers = array_shift($data);
    

    return view('csv.create-type', compact('csvFile', 'headers'));
}

public function storeType(Request $request)
{
    $request->validate([
        'csv_file_id' => 'required|exists:csv_files,id',
        'type_name' => 'required|string',
        'columns' => 'required|array',
    ]);

    // Create the CsvType entry
    CsvType::create([
        'csv_file_id' => $request->csv_file_id,
        'type_name' => strtolower($request->type_name),
        'columns' => $request->columns,
    ]);

    // ✅ Now regenerate subfiles
    $csvFile = CsvFile::findOrFail($request->csv_file_id);
    $path = storage_path('app/public/csvs/' . basename($csvFile->filename));
    $base = pathinfo($csvFile->filename, PATHINFO_FILENAME);

    $this->generateSubFiles($path, $base);  // Ensure this method is not static

    return redirect()->route('csv.index')->with('success', 'Marketplace type created and subfile generated.');
}


public function listMarketplaces($csvFileId)
{
    $csvFile = CsvFile::findOrFail($csvFileId);
    $marketplaces = CsvType::where('csv_file_id', $csvFileId)->get();

    return view('csv.marketplaces', compact('csvFile', 'marketplaces'));
}




}
