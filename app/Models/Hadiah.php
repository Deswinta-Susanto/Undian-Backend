<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hadiah extends Model
{
    use HasFactory;

    protected $table = 'doorprizes';

    // Kolom yang dapat diisi
    protected $fillable = ['nama_hadiah', 'sponsor', 'jumlah_awal', 'kategori', 'jumlah_keluar', 'gambar'];

    // Casting untuk memastikan tipe data yang tepat
    protected $casts = [
        'jumlah_awal' => 'integer',
        'jumlah_keluar' => 'integer',
    ];

    // Accessor untuk menghitung jumlah sisa
    public function getJumlahSisaAttribute()
    {
        // Menghitung jumlah sisa berdasarkan jumlah_awal dan jumlah_keluar
        return $this->jumlah_awal - ($this->jumlah_keluar ?? 0);
    }
}
