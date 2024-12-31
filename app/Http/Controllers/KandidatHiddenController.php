<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KandidatHadiahHidden;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KandidatHadiahHiddenExport;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KandidatHiddenController extends Controller
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
    
        // Mengambil parameter 'q' dari query string
        $search = $request->query('q');
        $status = $request->query('status'); // Mengambil parameter 'status' dari query string
    
        // Membuat query dasar
        $kandidatQuery = KandidatHadiahHidden::query();
    
        // Jika ada parameter 'q', maka filter data berdasarkan nama, nipp, atau unit
        if ($search) {
            $kandidatQuery->where(function($query) use ($search) {
                $query->where('nama', 'like', "%$search%")
                    ->orWhere('nipp', 'like', "%$search%")
                    ->orWhere('unit', 'like', "%$search%");
            });
        }
    
        // Jika ada parameter 'status', maka filter data berdasarkan status
        if ($status) {
            $kandidatQuery->where('status', $status);
        }
    
        // Ambil data kandidat berdasarkan query yang telah dibangun
        $kandidat = $kandidatQuery->get();
    
        // Kembalikan data kandidat
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $kandidat
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
            // Import data dari file Excel
            Excel::import(new KandidatHadiahHiddenImport, $request->file('file'));

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
        // Mendownload file Excel dari data KandidatHadiahHidden
        return Excel::download(new KandidatHadiahHiddenExport, 'kandidat_hadiah_hidden.xlsx');
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
                'status' => 'nullable|string',
            ]);
    
            // Tetapkan status default jika tidak diisi
            $validatedData['status'] = $request->input('status', 'belum menang');
    
            // Buat data kandidat
            $kandidat = KandidatHadiahHidden::create($validatedData);
    
            // Response 201 Created ketika berhasil
            return response()->json([
                'message' => 'Kandidat Hadiah Hidden created successfully',
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
        // Menampilkan data berdasarkan ID
        return KandidatHadiahHidden::findOrFail($id);
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
                'unit' => 'required|string',
                'status' => 'nullable|string|in:sudah menang,belum menang',
            ]);

            // Temukan kandidat berdasarkan ID
            $kandidat = KandidatHadiahHidden::findOrFail($id);

            // Update data kandidat
            $kandidat->update($validatedData);

            return response()->json([
                'message' => 'Kandidat Hadiah Hidden updated successfully',
                'data' => $kandidat
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kandidat Hadiah Hidden not found'
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
            $kandidat = KandidatHadiahHidden::findOrFail($id);

            // Hapus kandidat
            $kandidat->delete();

            return response()->json([
                'message' => 'Kandidat Hadiah Hidden deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kandidat Hadiah Hidden not found'
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
            KandidatHadiahHidden::truncate();

            return response()->json([
                'message' => 'All Kandidat Hadiah Hidden deleted successfully'
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
    //         $query = KandidatHadiahHidden::query();
    
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
        // Ambil nama-nama kandidat hidden
        $kandidatHiddenNames = KandidatHadiahHidden::pluck('nama');
        
        // Filter data yang statusnya belum menang
        $kandidatHidden = KandidatHadiahHidden::where('status', 'belum menang')
            ->whereIn('nama', $kandidatHiddenNames)
            ->get();
        
        return response()->json($kandidatHidden);
    }
}
