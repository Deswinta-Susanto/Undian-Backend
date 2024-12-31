<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KandidatHadiahUmum;
use App\Imports\KandidatHadiahUmumImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KandidatHadiahUmumExport;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class KandidatHadiahUmumController extends Controller
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
    
        // Mengambil parameter pencarian q dan status
        $query = $request->get('q');
        $status = $request->get('status'); // Menambahkan parameter status
    
        // Membuat query dasar
        $kandidatQuery = KandidatHadiahUmum::query();
    
        // Jika ada parameter pencarian q
        if ($query) {
            // Pencarian berdasarkan nama
            $kandidatQuery->where('nama', 'like', '%' . $query . '%');
        }
    
        // Jika ada parameter status
        if ($status) {
            // Pencarian berdasarkan status
            $kandidatQuery->where('status', 'like', '%' . $status . '%');
        }
    
        // Eksekusi query dan ambil data kandidat
        $kandidat = $kandidatQuery->get();
    
        // Hitung jumlah kandidat
        $jumlahKandidat = $kandidat->count();
    
        // Kembalikan data kandidat dengan jumlahnya
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $kandidat,
            'count' => $jumlahKandidat // Menambahkan jumlah kandidat
        ], 200);
    }
    
    public function importExcel(Request $request)
    {
        $admin = Auth::guard('api')->user();
    
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
            Excel::import(new KandidatHadiahUmumImport, $request->file('file'));
    
            return response()->json([
                'message' => 'File uploaded and data imported successfully.',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors in file.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors in Excel file.',
                'errors' => $e->failures(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Excel import error: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Error uploading or importing file.',
                'error' => $e->getMessage(),
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
        return Excel::download(new KandidatHadiahUmumExport, 'kandidat_hadiah_umum.xlsx');
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
                'jabatan' => 'nullable|string',
                'unit' => 'required|string',
            ]);
    
            // Cek apakah data dengan NIPP yang sama sudah ada
            if (KandidatHadiahUmum::where('nipp', $validatedData['nipp'])->exists()) {
                return response()->json([
                    'message' => 'Kandidat dengan NIPP ini sudah ada.'
                ], 409); // 409 Conflict jika data sudah ada
            }
    
            // Tetapkan nilai default untuk status jika tidak diisi
            $validatedData['status'] = $request->input('status', 'belum menang');
    
            // Buat data kandidat
            $kandidat = KandidatHadiahUmum::create($validatedData);
    
            return response()->json([
                'message' => 'Kandidat Hadiah Umum created successfully',
                'data' => $kandidat
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
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
        return KandidatHadiahUmum::findOrFail($id);
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
    
    \Log::info('Update request received', ['id' => $id, 'data' => $request->all()]);
    
    try {
        // Validasi request
        $validatedData = $request->validate([
            'nama' => '|string',
            'nipp' => '|string',
            'jabatan' => '|string',
            'unit' => '|string',
            'status' => 'nullable|in:sudah menang,belum menang',
        ]);

        // Temukan kandidat berdasarkan ID atau gagal
        $kandidat = KandidatHadiahUmum::findOrFail($id);

        // Tetapkan status default jika tidak diubah dalam request
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = 'belum menang';
        }

        // Update data kandidat
        $kandidat->update($validatedData);

        \Log::info('Kandidat updated successfully', ['id' => $id, 'data' => $kandidat]);

        return response()->json([
            'message' => 'Kandidat Hadiah Umum updated successfully',
            'data' => $kandidat
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Kandidat not found', ['id' => $id]);
        return response()->json([
            'message' => 'Kandidat Hadiah Umum not found'
        ], 404);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 403);

    } catch (\Exception $e) {
        \Log::error('Update failed', ['error' => $e->getMessage()]);
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
        // Temukan kandidat berdasarkan ID atau gagal
        $kandidat = KandidatHadiahUmum::findOrFail($id);
    
        // Hapus kandidat
        $kandidat->delete();
    
        // Response 200 OK dengan pesan konfirmasi
        return response()->json([
            'message' => 'Kandidat Hadiah Umum deleted successfully'
        ], 200);
    
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Response 404 Not Found jika kandidat tidak ditemukan
        return response()->json([
            'message' => 'Kandidat Hadiah Umum not found'
        ], 404);
    
    } catch (\Exception $e) {
        // Response 500 Internal Server Error jika ada error lainnya
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
        KandidatHadiahUmum::truncate();

        return response()->json([
            'message' => 'All Kandidat Hadiah Umum deleted successfully'
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
//         // Validasi parameter pencarian dari request body
//         $validatedData = $request->validate([
//             'search' => 'nullable|string',
//             'status' => 'nullable|string|in:belum menang,sudah menang',
//         ]);

//         // Ambil parameter pencarian dari request body
//         $search = $validatedData['search'] ?? null;
//         $status = $validatedData['status'] ?? null;

//         // Mulai query builder
//         $query = KandidatHadiahUmum::query();

//         // Terapkan filter jika parameter pencarian disediakan
//         if ($search) {
//             $query->where(function($q) use ($search) {
//                 $q->where('nama', 'like', "%$search%")
//                   ->orWhere('nipp', 'like', "%$search%")
//                 //   ->orWhere('jabatan', 'like', "%$search%")
//                   ->orWhere('unit', 'like', "%$search%");
//             });
//         }

//         // Terapkan filter status jika disediakan
//         if ($status) {
//             $query->where('status', $status);
//         }

//         // Ambil data kandidat yang sudah difilter
//         $dataKandidat = $query->get();

//         return response()->json([
//             'kandidat' => $dataKandidat,    // Data kandidat yang sesuai filter
//         ], 200);
//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Server Error',
//             'message' => $e->getMessage(),
//         ], 500);
//     }
// }
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
            'winner_ids.*' => 'exists:kandidat_umum,id', // Memastikan setiap ID ada di database
        ]);

        // Perbarui status untuk semua kandidat dengan ID yang diberikan
        KandidatHadiahUmum::whereIn('id', $validatedData['winner_ids'])
            ->update(['status' => $validatedData['status']]);

        // Ambil data kandidat yang diperbarui (opsional)
        $updatedKandidat = KandidatHadiahUmum::whereIn('id', $validatedData['winner_ids'])->get();

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
