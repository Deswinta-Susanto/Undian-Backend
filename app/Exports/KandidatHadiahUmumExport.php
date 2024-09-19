<?php

namespace App\Exports;

use App\Models\KandidatHadiahUmum;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandidatHadiahUmumExport implements FromCollection, WithHeadings
{
    // Mengambil semua data KandidatHadiahUtama untuk diexport
    public function collection()
    {
        return KandidatHadiahUmum::all();
    }

    // Menambahkan heading (kolom pertama di Excel)
    public function headings(): array
    {
        return [
            'ID',
            'NIPP',
            'Nama',
            'unit',
            'Status',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }
}
