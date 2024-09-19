<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Riwayat;
use App\Models\Doorprize;
use Illuminate\Support\Facades\Log;
use App\Exports\RiwayatExport;
use Maatwebsite\Excel\Facades\Excel;

class RiwayatPemenangController extends Controller
{
    public function export()
    {
        try {
            // Ambil data riwayat dengan nama hadiah
            $riwayatData = Riwayat::join('doorprizes', 'riwayat.doorprize_id', '=', 'doorprizes.id')
                ->select('riwayat.id', 'riwayat.event_id', 'riwayat.doorprize_id', 'riwayat.pemenang', 'riwayat.nipp', 'riwayat.unit','riwayat.created_at', 'riwayat.updated_at', 'doorprizes.nama_hadiah')
                ->get()
                ->map(function($item) {
                    return [
                        $item->id,
                        $item->event_id,
                        $item->doorprize_id,
                        $item->pemenang,
                        $item->nipp,
                        $item->unit,
                        $item->created_at,
                        $item->updated_at,
                        $item->nama_hadiah, // Pastikan nama_hadiah disertakan
                    ];
                })
                ->toArray();
    
            return Excel::download(new RiwayatExport($riwayatData), 'riwayat.xlsx');
        } catch (\Exception $e) {
            Log::error('Error during export: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during export: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    // Fungsi untuk menambahkan riwayat pemenang
    public function addRiwayat(Request $request)
    {
        // Validasi data yang masuk
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'doorprize_id' => 'required|integer|exists:doorprizes,id',
            'pemenang' => 'required|string|max:255',
            'nipp' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'nama_hadiah' => 'required|string|max:255',  // Pastikan nama_hadiah juga ada dalam validasi
        ]);
    
        try {
            // Simpan riwayat dengan data yang telah divalidasi
            $riwayat = Riwayat::create([
                'event_id' => $validated['event_id'],
                'doorprize_id' => $validated['doorprize_id'],
                'pemenang' => $validated['pemenang'],
                'nipp' => $validated['nipp'],
                'unit' => $validated['unit'],
                'nama_hadiah' => $validated['nama_hadiah'], // Simpan nama hadiah
            ]);
    
            // Berikan respon sukses dengan data riwayat yang disimpan
            return response()->json(['success' => true, 'data' => $riwayat], 201);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan saat menyimpan data, berikan respon error
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan riwayat: ' . $e->getMessage()], 500);
        }
    }
    
    // Fungsi untuk menghapus riwayat pemenang berdasarkan id
    public function deleteRiwayat($id)
    {
        // Temukan riwayat berdasarkan id
        $riwayat = Riwayat::find($id);

        // Jika riwayat tidak ditemukan, kembalikan response 404
        if (!$riwayat) {
            return response()->json(['success' => false, 'message' => 'Riwayat tidak ditemukan'], 404);
        }

        // Hapus riwayat
        $riwayat->delete();

        return response()->json(['success' => true, 'message' => 'Riwayat berhasil dihapus'], 200);
    }

    // Fungsi untuk mendapatkan semua riwayat pemenang
    public function getAllRiwayat()
    {
        // Lakukan join antara tabel 'riwayat' dan 'doorprizes' untuk mendapatkan 'nama_hadiah'
        $riwayat = Riwayat::join('doorprizes', 'riwayat.doorprize_id', '=', 'doorprizes.id')
            ->select('riwayat.*', 'doorprizes.nama_hadiah')
            ->get();

        return response()->json(['success' => true, 'data' => $riwayat], 200);
    }

    // Fungsi untuk menghapus semua riwayat pemenang
    public function deleteRiwayatAll()
    {
        // Hapus semua entri di tabel riwayat
        Riwayat::truncate();

        return response()->json(['success' => true, 'message' => 'Semua riwayat berhasil dihapus'], 200);
    }
}
