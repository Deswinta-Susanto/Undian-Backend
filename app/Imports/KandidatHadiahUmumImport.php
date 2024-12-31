<?php
namespace App\Imports;

use App\Models\KandidatHadiahUmum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;

class KandidatHadiahUmumImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function chunkSize(): int
    {
        return 1000;
    }
    public function model(array $row)
    {

        try {
            // Log input row for debugging
            Log::info('Processing row: ', $row);
    
            // Cek apakah data dengan kombinasi nama dan nipp sudah ada
            $existingKandidat = KandidatHadiahUmum::where('nama', $row['nama'])
                ->where('nipp', $row['nipp'])
                ->exists();
    
            // Jika data sudah ada, return null supaya tidak ditambahkan lagi
            if ($existingKandidat) {
                Log::info('Duplicate entry skipped: ', ['nama' => $row['nama'], 'nipp' => $row['nipp']]);
                return null;
            }
    
            // Jika data tidak ada, tambahkan data baru
            return new KandidatHadiahUmum([
                'nipp'    => $row['nipp'],
                'nama'    => $row['nama'],
                'unit'    => $row['unit'],
                'status'  => $row['status'] ?? 'belum menang',
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error processing row: ' . $e->getMessage(), $row);
            return null; // Skip the row on error
        }
    }
}