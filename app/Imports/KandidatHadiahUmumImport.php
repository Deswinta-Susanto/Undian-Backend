<?php
namespace App\Imports;

use App\Models\KandidatHadiahUmum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KandidatHadiahUmumImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Cek apakah data dengan kombinasi nama dan nipp sudah ada
        $existingKandidat = KandidatHadiahUmum::where('nama', $row['nama'])
            ->where('nipp', $row['nipp'])
            ->exists();

        // Jika data sudah ada, return null supaya tidak ditambahkan lagi
        if ($existingKandidat) {
            return null;
        }

        // Jika data tidak ada, tambahkan data baru
        return new KandidatHadiahUmum([
         
            'nipp'    => $row['nipp'],
            'nama'    => $row['nama'],
            'unit'    => $row['unit'],
            'status'  => $row['status'] ?? 'belum menang',
        ]);
    }
}
