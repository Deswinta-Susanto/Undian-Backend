<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KandidatHadiahUtama; // Menggunakan model KandidatHadiahUtama
use App\Imports\KandidatHadiahUtamaImport; // Menggunakan import untuk KandidatHadiahUtama
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KandidatHadiahUtamaExport;
use App\Models\KandidatHadiahUmum;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KandidatHadiahUtamaController extends Controller
{
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

    // Mengambil parameter pencarian q
    $query = $request->get('q');
    $status = $request->get('status');

    $kandidatQ = KandidatHadiahUtama::query();
    
    // Jika ada parameter pencarian q
    if ($query) {
        // Pencarian berdasarkan nama
        $kandidatQ->where('nama', 'like', '%' . $query . '%');
    
    }         if ($status) {
        // Pencarian berdasarkan status
        $kandidatQ->where('status', 'like', '%' . $status . '%');
    }

    $kandidat = $kandidatQ->get();

    $jumlahKandidat = $kandidat->count();

    // Kembalikan data kandidat
    return response()->json([
        'message' => 'Data retrieved successfully',
        'data' => $kandidat,
        'count' => $jumlahKandidat
    ], 200);
}

    public function importExcel(Request $request)
    {
        $admin = Auth::guard('api')->user(); 
            
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            Excel::import(new KandidatHadiahUtamaImport, $request->file('file'));

            return response()->json([
                'message' => 'File uploaded and data imported successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error uploading or importing file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportToExcel()
    {
        $admin = Auth::guard('api')->user(); 
            
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        // Mendownload file Excel dari data KandidatHadiahUtama
        return Excel::download(new KandidatHadiahUtamaExport, 'kandidat_hadiah_utama.xlsx');
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
        try {
            // Validasi request
            $validatedData = $request->validate([
                'nama' => 'required|string',
                'nipp' => 'required|string',
                'jabatan' => '|string',
                'unit' => 'required|string',
            ]);

            // Cek apakah data dengan NIPP yang sama sudah ada
            $existingKandidat = KandidatHadiahUtama::where('nipp', $validatedData['nipp'])->exists();

            if ($existingKandidat) {
                return response()->json([
                    'message' => 'Kandidat dengan NIPP ini sudah ada.'
                ], 409); // 409 Conflict jika data sudah ada
            }

            // Tetapkan nilai default untuk status jika tidak diisi
            $validatedData['status'] = $request->input('status', 'belum menang');

            // Buat data kandidat
            $kandidat = KandidatHadiahUtama::create($validatedData);

            // Response 201 Created ketika berhasil
            return response()->json([
                'message' => 'Kandidat Hadiah Utama created successfully',
                'data' => $kandidat
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Response 403 Forbidden jika validasi gagal
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 403);

        } catch (\Exception $e) {
            // Response 500 Internal Server Error jika ada error lainnya
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
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
        return KandidatHadiahUtama::findOrFail($id);
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
        try {
            // Validasi request
            $validatedData = $request->validate([
                'nama' => 'required|string',
                'nipp' => 'required|string',
                'jabatan' => '|string',
                'unit' => 'required|string',
                'status' => 'nullable|in:sudah menang,belum menang',
            ]);

            // Temukan kandidat berdasarkan ID
            $kandidat = KandidatHadiahUtama::findOrFail($id);

            // Tetapkan status default jika tidak diubah
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = 'belum menang';
            }

            // Update data kandidat
            $kandidat->update($validatedData);

            return response()->json([
                'message' => 'Kandidat Hadiah Utama updated successfully',
                'data' => $kandidat
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kandidat Hadiah Utama not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
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
        try {
            // Temukan kandidat berdasarkan ID
            $kandidat = KandidatHadiahUtama::findOrFail($id);

            // Hapus kandidat
            $kandidat->delete();

            return response()->json([
                'message' => 'Kandidat Hadiah Utama deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kandidat Hadiah Utama not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyAll()
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
        // Hapus semua data kandidat
        KandidatHadiahUtama::truncate();

        return response()->json([
            'message' => 'All Kandidat Hadiah utama deleted successfully'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // public function filterKandidat(Request $request)
    // {
    //     $admin = Auth::guard('api')->user(); 
            
    //     // Memeriksa apakah admin terautentikasi
    //     if (!$admin) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized: Invalid token',
    //         ], 401);
    //     }
    //     try {
    //         // Validasi parameter filter dari body
    //         $validatedData = $request->validate([
    //             'search' => 'nullable|string',
    //             'status' => 'nullable|string|in:belum menang,sudah menang',
    //         ]);
    
    //         $search = $validatedData['search'] ?? null;
    //         $status = $validatedData['status'] ?? null;
    
    //         // Query untuk filter kandidat
    //         $query = KandidatHadiahUtama::query();
    
    //         if ($search) {
    //             $query->where(function($q) use ($search) {
    //                 $q->where('nama', 'like', "%$search%")
    //                   ->orWhere('nipp', 'like', "%$search%")
                   
    //                   ->orWhere('unit', 'like', "%$search%");
    //             });
    //         }
    
    //         if ($status) {
    //             $query->where('status', $status);
    //         }
    
    //         $dataKandidat = $query->get();
    
    //         return response()->json([
    //             'kandidat' => $dataKandidat,
    //         ], 200);
    
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Server Error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function getKandidatForSpinner(): JsonResponse
{
    $admin = Auth::guard('api')->user(); 
            
    // Memeriksa apakah admin terautentikasi
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid token',
        ], 401);
    }
    // Ambil nama-nama kandidat utama
    $kandidatUtamaNames = KandidatHadiahUtama::pluck('nama');
    \Log::info('Kandidat Utama Names:', $kandidatUtamaNames->toArray());

    // Cek format dan isi dari $kandidatUtamaNames
    $kandidatUtamaNamesArray = $kandidatUtamaNames->map(function($name) {
        return trim($name); // Menghilangkan spasi yang tidak diinginkan
    })->filter()->unique()->toArray();
    \Log::info('Trimmed Unique Kandidat Utama Names:', $kandidatUtamaNamesArray);

    // Filter kandidat umum yang statusnya belum menang dan nama-namanya ada di kandidat utama
    $kandidatUmum = KandidatHadiahUmum::where('status', 'belum menang')
        ->whereIn('nama', $kandidatUtamaNamesArray)
        ->get();
    
    \Log::info('Filtered Kandidat Umum:', $kandidatUmum->toArray());

    return response()->json($kandidatUmum);
}


public function editStatus(Request $request)
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
        // Validasi input status dan winner_ids
        $validatedData = $request->validate([
            'status' => 'required|string|in:sudah menang,belum menang',
            'winner_ids' => 'required|array', // Memastikan winner_ids adalah array
            'winner_ids.*' => 'exists:kandidat_utama,id', // Memastikan setiap ID ada di database
        ]);

        // Perbarui status untuk semua kandidat dengan ID yang diberikan
        KandidatHadiahUtama::whereIn('id', $validatedData['winner_ids'])
            ->update(['status' => $validatedData['status']]);

        // Ambil data kandidat yang diperbarui (opsional)
        $updatedKandidat = KandidatHadiahUtama::whereIn('id', $validatedData['winner_ids'])->get();

        // Response sukses
        return response()->json([
            'message' => 'Status kandidat updated successfully',
            'data' => $updatedKandidat
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Jika validasi gagal, kembalikan response 403
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 403);

    } catch (\Exception $e) {
        // Jika ada error lainnya, kembalikan response 500
        return response()->json([
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
