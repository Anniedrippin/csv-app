<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvType extends Model
{
    protected $fillable = [
        'csv_file_id',
        'type_name',
        'columns',
    ];

    protected $casts = [
        'columns' => 'array', // automatically cast JSON to array and back
    ];

    public function csvFile()
    {
        return $this->belongsTo(CsvFile::class);
    }
}
