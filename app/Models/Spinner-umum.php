<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinnerUmum extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang digunakan oleh model
    protected $table = 'spinner_umum';

    // Tentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'id_event',
        'jumlah_hadiah',
        'tanggal_spin',
    ];

    // Relasi dengan tabel 'events'
    public function event()
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    // Relasi dengan tabel 'hasil_spinner'
    public function hasilSpinner()
    {
        return $this->hasMany(HasilSpinner::class, 'id_spinner');
    }

    // Relasi untuk mendapatkan kandidat yang belum menang dari 'kandidat_hadiah_umum'
    public function kandidatBelumMenang()
    {
        return $this->hasMany(KandidatHadiahUmum::class, 'id', 'id_kandidat')
                    ->where('status', 'belum menang');
    }
}
