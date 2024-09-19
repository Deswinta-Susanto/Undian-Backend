<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KandidatHadiahUtama extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nipp',
   
        'unit',
        'status',
    ];

    // Nama tabel di database
    protected $table = 'kandidat_utama'; // Perbaiki nama tabel di sini, hilangkan spasi ekstra
}
