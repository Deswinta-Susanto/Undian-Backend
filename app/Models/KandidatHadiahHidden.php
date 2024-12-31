<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KandidatHadiahHidden extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nipp',
        'unit',
        'status',
        'jabatan',
    ];

    // Nama tabel di database
    protected $table = 'kandidahidden'; // Perbaiki nama tabel di sini, hilangkan spasi ekstra
}
