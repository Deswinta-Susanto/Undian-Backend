<?php

namespace App\Exports;

use App\Models\KandidatHadiahHidden;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandidatHiddenExport implements FromCollection, WithHeadings
{
    // Mengambil semua data KandidatHadiahUtama untuk diexport
    public function collection()
    {
        return KandidatHadiahHidden::all();
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
