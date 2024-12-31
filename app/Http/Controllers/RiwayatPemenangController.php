<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Riwayat;
use App\Models\Event;
use App\Models\Hadiah;
use App\Models\KandidatHadiahUmum;
use App\Models\KandidatHadiahUtama; 
use App\Models\KandidatHadiahHidden;
use Illuminate\Support\Facades\Log;
use App\Exports\RiwayatExport;
use Maatwebsite\Excel\Facades\Excel;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response; 

class RiwayatPemenangController extends Controller
{
    public function export()
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
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
                        $item->nama_hadiah, 
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
    
    public function addRiwayat(Request $request)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        
        // Validasi bahwa request berisi array riwayat
        $validated = $request->validate([
            'riwayat' => 'required|array', // Pastikan 'riwayat' berupa array
            'riwayat.*.event_id' => 'required|integer|exists:events,id',  // Validasi untuk setiap item di dalam array
            'riwayat.*.doorprize_id' => 'required|integer|exists:doorprizes,id',
            'riwayat.*.nipp' => 'required|string|max:255',
        ]);
    
        try {
            // Lakukan bulk insert data riwayat
            $riwayatData = [];
            $doorprizeCounts = [];
    
            foreach ($validated['riwayat'] as $data) {
                // Ambil data tambahan dari database
                $event = Event::find($data['event_id']);
                $doorprize = Hadiah::find($data['doorprize_id']);
                
                // Cari pemenang di ketiga model
                $pemenang = KandidatHadiahUmum::where('nipp', $data['nipp'])->first()
                            ?? KandidatHadiahUtama::where('nipp', $data['nipp'])->first()
                            ?? KandidatHadiahHidden::where('nipp', $data['nipp'])->first();
    
                if (!$pemenang) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pemenang tidak ditemukan',
                    ], 404);
                }
    
                $riwayatData[] = [
                    'event_id' => $data['event_id'],
                    'doorprize_id' => $data['doorprize_id'],
                    'pemenang' => $pemenang->nama,
                    'nipp' => $data['nipp'],
                    'unit' => $pemenang->unit,
                    'nama_hadiah' => $doorprize->nama_hadiah,
                    'created_at' => now(),  // Tambahkan timestamp created_at
                    'updated_at' => now(),  // Tambahkan timestamp updated_at
                ];
    
                // Update status kandidat menjadi 'sudah menang' di semua model
                KandidatHadiahUmum::where('nipp', $data['nipp'])->update(['status' => 'sudah menang']);
                KandidatHadiahUtama::where('nipp', $data['nipp'])->update(['status' => 'sudah menang']);
                KandidatHadiahHidden::where('nipp', $data['nipp'])->update(['status' => 'sudah menang']);
    
                // Hitung jumlah doorprize yang digunakan
                if (!isset($doorprizeCounts[$data['doorprize_id']])) {
                    $doorprizeCounts[$data['doorprize_id']] = 0;
                }
                $doorprizeCounts[$data['doorprize_id']]++;
            }
    
            // Insert data riwayat ke database
            Riwayat::insert($riwayatData);
    
            // Kurangi jumlah hadiah sesuai dengan jumlah yang digunakan
            foreach ($doorprizeCounts as $doorprize_id => $count) {
                $doorprize = Hadiah::find($doorprize_id);
                $doorprize->increment('jumlah_keluar', $count);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Riwayat pemenang berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            Log::error('Error during insert: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during insert: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // Fungsi untuk menghapus riwayat pemenang berdasarkan id
    public function deleteRiwayat($id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
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

    public function getAllRiwayat(Request $request)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
    
        // Mengambil parameter 'q' dari query string
        $search = $request->query('q');
    
        // Jika ada parameter 'q', maka filter data berdasarkan nama_hadiah atau pemenang
        if ($search) {
            $riwayat = Riwayat::join('doorprizes', 'riwayat.doorprize_id', '=', 'doorprizes.id')
                ->where('doorprizes.nama_hadiah', 'like', "%$search%")
                ->orWhere('riwayat.pemenang', 'like', "%$search%")
                ->select('riwayat.*', 'doorprizes.nama_hadiah')
                ->get();
        } else {
            // Jika tidak ada parameter 'q', ambil semua data riwayat
            $riwayat = Riwayat::join('doorprizes', 'riwayat.doorprize_id', '=', 'doorprizes.id')
                ->select('riwayat.*', 'doorprizes.nama_hadiah')
                ->get();
        }
    
        // Kembalikan data riwayat
        return response()->json([
            'success' => true,
            'data' => $riwayat
        ], 200);
    }
    
    // Fungsi untuk menghapus semua riwayat pemenang
    public function deleteRiwayatAll()
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        // Hapus semua entri di tabel riwayat
        Riwayat::truncate();

        return response()->json(['success' => true, 'message' => 'Semua riwayat berhasil dihapus'], 200);
    }
}
