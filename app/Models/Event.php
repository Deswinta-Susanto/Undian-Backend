<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_event',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $dates = [
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    // Accessor untuk menentukan status event
    public function getStatusAttribute()
    {
        $now = now();
        if ($this->tanggal_mulai <= $now && $this->tanggal_selesai >= $now) {
            return 'on';
        }
        return 'off';
    }
    
}
