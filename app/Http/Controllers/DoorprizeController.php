<?php

namespace App\Http\Controllers;

use App\Models\Hadiah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DoorprizeImport;

class DoorprizeController extends Controller
{
 // Validation logic moved to a separate function
    protected function validateHadiah($request) {
        return $request->validate([
            'nama_hadiah' => 'required|string',
            'sponsor' => 'nullable|string',
            'jumlah_awal' => 'required|integer|min:1',
            'kategori' => 'required|string',
            'gambar' => 'nullable|string|url', 
        ]);
    }
    public function store(Request $request)
    {
        $admin = Auth::guard('api')->user();
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }

        // Validasi input
        $validatedData = $this->validateHadiah($request);

        try {
            // Simpan data hadiah ke database
            $hadiah = Hadiah::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil ditambahkan',
                'data' => $hadiah
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error during insert: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during insert: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
    
        // Mengambil parameter pencarian q dan kategori
        $query = $request->get('q');
        $kategori = $request->get('kategori');
    
        // Jika ada parameter pencarian
        if ($query || $kategori) {
            // Pencarian berdasarkan nama_hadiah dan/atau kategori
            $hadiah = Hadiah::query()
                            ->when($query, function ($q) use ($query) {
                                $q->where('nama_hadiah', 'like', '%' . $query . '%');
                            })
                            ->when($kategori, function ($q) use ($kategori) {
                                $q->where('kategori', $kategori);
                            })
                            ->get();
        } else {
            // Ambil semua hadiah jika tidak ada pencarian
            $hadiah = Hadiah::all();
        }
    
        // Memformat hadiah untuk response
        $formattedHadiah = $hadiah->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_hadiah' => $item->nama_hadiah,
                'sponsor' => $item->sponsor,
                'jumlah_awal' => $item->jumlah_awal,
                'jumlah_sisa' => $item->jumlah_sisa,
                'kategori' => $item->kategori,
                'gambar' => $item->gambar,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    
        return response()->json(['success' => true, 'data' => $formattedHadiah]);
    }
    

    public function show($id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        $hadiah = Hadiah::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hadiah->id,
                'nama_hadiah' => $hadiah->nama_hadiah,
                'sponsor' => $hadiah->sponsor,
                'jumlah_awal' => $hadiah->jumlah_awal,
                'jumlah_sisa' => $hadiah->jumlah_sisa,
                'kategori' => $hadiah->kategori,
                'gambar' => $hadiah->gambar,
                'created_at' => $hadiah->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $hadiah->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::guard('api')->user();
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }

        // Validasi input
        $validatedData = $this->validateHadiah($request);

        try {
            // Temukan hadiah berdasarkan ID
            $hadiah = Hadiah::findOrFail($id);

            // Perbarui data hadiah di database
            $hadiah->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Hadiah berhasil diperbarui',
                'data' => $hadiah
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error during update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during update: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateJumlahKeluar($id, Request $request)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        Log::info('Memproses updateJumlahKeluar untuk ID: ' . $id);

        $request->validate([
            'jumlah_keluar' => 'required|integer|min:1',
        ]);

        $hadiah = Hadiah::findOrFail($id);

        $jumlahKeluarInput = $request->input('jumlah_keluar');
        $hadiah->jumlah_keluar = $hadiah->jumlah_keluar + $jumlahKeluarInput;
        $hadiah->save();

        Log::info('Jumlah keluar setelah update: ' . $hadiah->jumlah_keluar);

        return response()->json(['success' => true, 'message' => 'Jumlah keluar hadiah updated successfully']);
    }

    public function destroy($id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        $hadiah = Hadiah::findOrFail($id);
        $hadiah->delete();

        return response()->json(['success' => true, 'message' => 'Hadiah berhasil dihapus!'], 200);
    }
//     public function filterByName(Request $request)
// {
//     try {
//         // Validasi input bahwa parameter 'search' harus ada dalam body request
//         $validatedData = $request->validate([
//             'search' => 'required|string|max:255'
//         ]);

//         // Cari hadiah berdasarkan nama yang mirip dengan input
//         $hadiah = Hadiah::where('nama_hadiah', 'like', '%' . $validatedData['search'] . '%')->get();

//         // Format data jika diperlukan
//         $formattedHadiah = $hadiah->map(function ($item) {
//             return [
//                 'id' => $item->id,
//                 'nama_hadiah' => $item->nama_hadiah,
//                 'kategori' => $item->kategori,
//                 'jumlah_awal' => $item->jumlah_awal,
//                 'jumlah_sisa' => $item->jumlah_sisa,
//                 'sponsor' => $item->sponsor,
//                 'gambar' => $item->gambar ? asset('storage/' . $item->gambar) : null,
//                 'created_at' => $item->created_at->format('Y-m-d H:i:s'),
//                 'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
//             ];
//         });

//         // Jika tidak ada hasil ditemukan
//         if ($formattedHadiah->isEmpty()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'No results found',
//                 'data' => []
//             ]);
//         }

//         // Kembalikan response dengan hasil filter dan metadata tambahan
//         return response()->json([
//             'success' => true,
//             'message' => 'Data fetched successfully',
//             'total' => $formattedHadiah->count(),
//             'data' => $formattedHadiah
//         ]);
//     } catch (\Exception $e) {
//         // Menangani kasus error lainnya
//         return response()->json([
//             'success' => false,
//             'message' => 'An error occurred: ' . $e->getMessage()
//         ], 500);
//     }
// }

}
