<?php
namespace App\Imports;

use App\Models\KandidatHadiahUtama;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KandidatHadiahUtamaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Cek apakah data dengan kombinasi nama dan nipp sudah ada
        $existingKandidat = KandidatHadiahUtama::where('nama', $row['nama'])
            ->where('nipp', $row['nipp'])
            ->exists();

        // Jika data sudah ada, return null supaya tidak ditambahkan lagi
        if ($existingKandidat) {
            return null;
        }

        // Jika data tidak ada, tambahkan data baru dengan status default 'belum menang'
        return new KandidatHadiahUtama([
            
            'nipp'    => $row['nipp'],
            'nama'    => $row['nama'],
            // 'jabatan' => $row['jabatan'],
            'unit'    => $row['unit'],
            'status'  => $row['status'] ?? 'belum menang',
        ]);
    }
}
