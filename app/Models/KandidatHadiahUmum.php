<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KandidatHadiahUmum extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nipp',
        'unit',
        'status',
    ];

    // Nama tabel di database
    protected $table = 'kandidat_umum'; // Perbaiki nama tabel di sini, hilangkan spasi ekstra
}
