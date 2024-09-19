<?php

namespace App\Exports;

use App\Models\KandidatHadiahUtama;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandidatHadiahUtamaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return KandidatHadiahUtama::all();
    }
    
    // Menambahkan heading (kolom pertama di Excel)
    public function headings(): array
    {
        return [
            'ID',
            'NIPP',
            'Nama',
            'Unit',
            'Status',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }
}

