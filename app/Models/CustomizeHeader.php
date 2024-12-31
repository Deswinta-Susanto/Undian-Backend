<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizeHeader extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'customize_header'; // Pastikan ini sesuai dengan nama tabel di database

    // Kolom yang bisa diisi
    protected $fillable = [
        'nama_header',
    ];

    // Aktifkan timestamps jika Anda memiliki `created_at` dan `updated_at`
    public $timestamps = true;
}
