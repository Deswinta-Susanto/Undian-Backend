<?php
namespace App\Imports;

use App\Models\Hadiah;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class DoorprizeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Check for required fields and log missing data
        if (empty($row['nama_hadiah']) || empty($row['jumlah_awal'])) {
            // Log missing fields instead of throwing an exception
            Log::warning('Missing required fields', [
                'nama_hadiah' => $row['nama_hadiah'],
                'jumlah_awal' => $row['jumlah_awal'],
                'row_data' => $row
            ]);
            
            // Optionally return null to skip this row
            return null;
        }

        // Create a new Hadiah model for valid data
        return new Hadiah([
            'nama_hadiah' => $row['nama_hadiah'],
            'sponsor' => $row['sponsor'],
            'jumlah_awal' => $row['jumlah_awal'],
            'kategori' => $row['kategori'],
            'gambar' => $row['gambar'],
        ]);
    }
}
