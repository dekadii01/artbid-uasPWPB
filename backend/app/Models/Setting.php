<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = [
        'key',   // Kolom kunci grup pengaturan
        'value', // Kolom nilai pengaturan dalam format JSON/Array
    ];

    protected $casts = [
        // Mengubah teks JSON di database menjadi aray
        'value' => 'array',
    ];
}
