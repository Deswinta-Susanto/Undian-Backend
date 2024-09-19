<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KandidatHadiahUmum;
use App\Imports\KandidatHadiahUmumImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KandidatHadiahUmumExport;

class KandidatHadiahUmumController extends Controller
{
    
    public function index()
{
    // Ambil semua data kandidat
    $kandidat = KandidatHadiahUmum::all();

    // Kembalikan data kandidat bersamaan dengan total sum dari id
    return response()->json([
        'message' => 'Data retrieved successfully',
        'data' => $kandidat
    ], 200);
}

public function importExcel(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xls,xlsx'
    ]);

    try {
        Excel::import(new KandidatHadiahUmumImport, $request->file('file'));

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
        // Mendownload file Excel dari data KandidatHadiahUtama
        return Excel::download(new KandidatHadiahUmumExport, 'kandidat_hadiah_umum.xlsx');
    }

    public function store(Request $request)
{
    try {
        // Validasi request
        $validatedData = $request->validate([
            'nama' => 'required|string',
            'nipp' => 'required|string',
            'jabatan' => '|string',
            'unit' => 'required|string',
        ]);

        // Cek apakah data dengan NIPP yang sama sudah ada
        $existingKandidat = KandidatHadiahUmum::where('nipp', $validatedData['nipp'])->exists();

        if ($existingKandidat) {
            return response()->json([
                'message' => 'Kandidat dengan NIPP ini sudah ada.'
            ], 409); // 409 Conflict jika data sudah ada
        }

        // Tetapkan nilai default untuk status jika tidak diisi
        $validatedData['status'] = $request->input('status', 'belum menang');

        // Buat data kandidat
        $kandidat = KandidatHadiahUmum::create($validatedData);

        // Response 201 Created ketika berhasil
        return response()->json([
            'message' => 'Kandidat Hadiah Umum created successfully',
            'data' => $kandidat
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Response 403 Forbidden jika validasi gagal
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 403);

    } catch (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
        // Response 405 Method Not Allowed
        return response()->json([
            'message' => 'Method Not Allowed'
        ], 405);

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
        return KandidatHadiahUmum::findOrFail($id);
    }
    
    
    public function update(Request $request, $id)
{
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


public function filterKandidat(Request $request)
{
    try {
        // Validasi parameter pencarian dari request body
        $validatedData = $request->validate([
            'search' => 'nullable|string',
            'status' => 'nullable|string|in:belum menang,sudah menang',
        ]);

        // Ambil parameter pencarian dari request body
        $search = $validatedData['search'] ?? null;
        $status = $validatedData['status'] ?? null;

        // Mulai query builder
        $query = KandidatHadiahUmum::query();

        // Terapkan filter jika parameter pencarian disediakan
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nipp', 'like', "%$search%")
                //   ->orWhere('jabatan', 'like', "%$search%")
                  ->orWhere('unit', 'like', "%$search%");
            });
        }

        // Terapkan filter status jika disediakan
        if ($status) {
            $query->where('status', $status);
        }

        // Ambil data kandidat yang sudah difilter
        $dataKandidat = $query->get();

        return response()->json([
            'kandidat' => $dataKandidat,    // Data kandidat yang sesuai filter
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function editStatus(Request $request, $id)
{
    try {
        // Validasi input status
        $validatedData = $request->validate([
            'status' => 'required|string|in:sudah menang,belum menang',
        ]);

        // Temukan kandidat berdasarkan ID
        $kandidat = KandidatHadiahUmum::findOrFail($id);

        // Perbarui status kandidat
        $kandidat->status = $validatedData['status'];
        $kandidat->save();

        // Response sukses
        return response()->json([
            'message' => 'Status kandidat updated successfully',
            'data' => $kandidat
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Jika kandidat tidak ditemukan, kembalikan response 404
        return response()->json([
            'message' => 'Kandidat Hadiah Umum not found'
        ], 404);

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
