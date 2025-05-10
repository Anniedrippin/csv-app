<?php

use App\Http\Controllers\CsvController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [CsvController::class, 'index'])->name('csv.index');
    Route::get('/csv/{id}', [CsvController::class, 'show'])->name('csv.show');
 

     Route::get('/csv/{id}/create-type', [CsvController::class, 'createTypeForm'])->name('csv.create-type');
    Route::post('/csv/store-type', [CsvController::class, 'storeType'])->name('csv.store-type');
    Route::get('/csv/{csvFileId}/marketplaces', [CsvController::class, 'listMarketplaces'])->name('csv.marketplaces');




    // Use Spatie's role middleware here
   Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/upload', [CsvController::class, 'upload'])->name('csv.upload');
    Route::post('/csv/save', [CsvController::class, 'save'])->name('csv.save');
    Route::get('/csv/{id}/export/{format}', [CsvController::class, 'export'])->name('csv.export');
    Route::delete('/csv/{id}', [CsvController::class, 'destroy'])->name('csv.destroy');
    Route::post('/csv/{id}/save-subfile', [CsvController::class, 'saveSubfile'])->name('csv.saveSubfile');

});

});
