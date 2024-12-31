<?php

namespace App\Http\Controllers;

use App\Models\CustomizeHeader; // Menggunakan model CustomizeHeader
use Illuminate\Http\Request;

class CustomizeHeaderController extends Controller
{
    // Fungsi untuk membuat data baru CustomizeHeader
    public function createCustomizeHeader(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'nama_header' => 'required|string|max:255',
        ]);

        // Buat data baru
        $customizeHeader = CustomizeHeader::create([
            'nama_header' => $validatedData['nama_header'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customize Header berhasil dibuat.',
            'data' => $customizeHeader
        ], 201);
    }

    // Fungsi untuk mendapatkan semua data CustomizeHeader
    public function getAllCustomizeHeaders()
    {
        // Ambil semua data customize_header
        $customizeHeaders = CustomizeHeader::all();

        return response()->json([
            'success' => true,
            'data' => $customizeHeaders
        ], 200);
    }

    // Fungsi untuk mendapatkan data CustomizeHeader berdasarkan ID
    public function getCustomizeHeaderById($id)
    {
        // Ambil data berdasarkan ID
        $customizeHeader = CustomizeHeader::find($id);

        if (!$customizeHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Customize Header tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $customizeHeader
        ], 200);
    }

    // Fungsi untuk mengupdate data CustomizeHeader berdasarkan ID
    public function updateCustomizeHeader(Request $request, $id)
    {
        // Validasi input
        $validatedData = $request->validate([
            'nama_header' => 'required|string|max:255',
        ]);

        // Cari data berdasarkan ID
        $customizeHeader = CustomizeHeader::find($id);

        if (!$customizeHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Customize Header tidak ditemukan.'
            ], 404);
        }

        // Update data
        $customizeHeader->update([
            'nama_header' => $validatedData['nama_header'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customize Header berhasil diperbarui.',
            'data' => $customizeHeader
        ], 200);
    }

    // Fungsi untuk menghapus CustomizeHeader berdasarkan ID
    public function deleteCustomizeHeader($id)
    {
        // Cari data berdasarkan ID
        $customizeHeader = CustomizeHeader::find($id);

        if (!$customizeHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Customize Header tidak ditemukan.'
            ], 404);
        }

        // Hapus data
        $customizeHeader->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customize Header berhasil dihapus.'
        ], 200);
    }
}
