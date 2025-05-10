<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvFile extends Model
{
    use HasFactory;

    
    protected $table = 'csv_files';

   
    protected $fillable = [
        'filename',
        'user_id',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class); 
    }
    public function types()
    {
    return $this->hasMany(CsvType::class);
    }

}
