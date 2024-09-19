<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model
    protected $table = 'riwayat';

    // Field yang dapat diisi secara massal
    protected $fillable = [
        'event_id',
        'doorprize_id',
        'pemenang',
        'nipp',
        'unit',
        'nama_hadiah',    // Tambahkan nama_hadiah ke field fillable
    ];

    // Relasi ke model Event
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // Relasi ke model Doorprize
    public function doorprize()
    {
        return $this->belongsTo(Doorprize::class, 'doorprize_id');
    }
}
