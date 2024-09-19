<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiwayatExport implements FromArray, WithHeadings
{
    protected $riwayatData;

    public function __construct(array $riwayatData)
    {
        $this->riwayatData = $riwayatData;
    }

    public function array(): array
    {
        return $this->riwayatData;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Event ID',
            'Doorprize ID',
            'Pemenang',
            'NIPP',
            'Unit',
            'Created At',
            'Updated At',
            'Nama Hadiah' // Pastikan heading sesuai dengan data
        ];
    }
}
