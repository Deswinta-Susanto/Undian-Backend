<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hadiah;

class DoorprizeController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_hadiah' => 'required|string|max:255',
            'sponsor' => 'nullable|string|max:255',
            'jumlah_awal' => 'required|integer|min:1',
            'kategori' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk gambar
        ]);
    
        $path = null;
        
        // Simpan gambar jika ada
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('images/hadiah', 'public'); // Simpan ke folder 'storage/app/public/images/hadiah'
        }
    
        $hadiah = Hadiah::create([
            'nama_hadiah' => $validatedData['nama_hadiah'],
            'sponsor' => $validatedData['sponsor'] ?? 'KAI',
            'jumlah_awal' => $validatedData['jumlah_awal'],
            'kategori' => $validatedData['kategori'],
            'gambar' => $path, // Simpan path gambar
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil dibuat!',
            'data' => $hadiah
        ], 201);
    }
    
    public function index()
    {
        $hadiah = Hadiah::all();
    
        $formattedHadiah = $hadiah->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_hadiah' => $item->nama_hadiah,
                'sponsor' => $item->sponsor,
                'jumlah_awal' => $item->jumlah_awal,
                'jumlah_sisa' => $item->jumlah_sisa,
                'kategori' => $item->kategori,
                'gambar' => $item->gambar ? asset('storage/' . $item->gambar) : null, // Menampilkan URL gambar
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $formattedHadiah
        ]);
    }
    

    public function show($id)
    {
        // Cari hadiah berdasarkan ID
        $hadiah = Hadiah::findOrFail($id);

        // Format data hadiah untuk menambahkan jumlah_sisa
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hadiah->id,
                'nama_hadiah' => $hadiah->nama_hadiah,
                'sponsor' => $hadiah->sponsor,
                'jumlah_awal' => $hadiah->jumlah_awal,
                'jumlah_sisa' => $hadiah->jumlah_sisa, // Menggunakan accessor jumlah_sisa
                'kategori' => $hadiah->kategori,
               'gambar' => $hadiah->gambar ? asset('storage/' . $hadiah->gambar) : null, // Menampilkan URL gambar
                'created_at' => $hadiah->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $hadiah->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function update(Request $request, $id)
{
    // Validasi input
    $validatedData = $request->validate([
        'nama_hadiah' => 'required|string|max:255',
        'sponsor' => 'nullable|string|max:255',
        'jumlah_awal' => 'required|integer|min:1',
        'kategori' => 'required|string|max:255',
        'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk gambar
    ]);

    // Cari data hadiah berdasarkan ID
    $hadiah = Hadiah::findOrFail($id);

    // Cek jika ada file gambar yang di-upload
    if ($request->hasFile('gambar')) {
        $path = $request->file('gambar')->store('images/hadiah', 'public');
        $validatedData['gambar'] = $path;
    }

    // Update data hadiah
    $hadiah->update($validatedData);

    // Kembalikan response sukses
    return response()->json([
        'success' => true,
        'message' => 'Hadiah berhasil diupdate!',
        'data' => $hadiah
    ], 200);
}


    public function destroy($id)
    {
        // Cari data hadiah berdasarkan ID
        $hadiah = Hadiah::findOrFail($id);

        // Hapus data hadiah
        $hadiah->delete();

        // Kembalikan response sukses setelah penghapusan
        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil dihapus!'
        ], 200);
    }

    public function filterHadiah(Request $request)
    {
        try {
            // Validasi bahwa parameter 'search' harus ada dalam body request
            $validatedData = $request->validate([
                'search' => 'required|string'
            ]);

            // Cari hadiah berdasarkan nama yang mirip dengan input
            $hadiah = Hadiah::where('nama_hadiah', 'like', '%' . $validatedData['search'] . '%')->get();

            // Format data jika diperlukan
            $formattedHadiah = $hadiah->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_hadiah' => $item->nama_hadiah,
                    'kategori' => $item->kategori,
                    'jumlah_awal' => $item->jumlah_awal,
                    'jumlah_sisa' => $item->jumlah_sisa, // Menggunakan accessor jumlah_sisa
                    'sponsor' => $item->sponsor,
                    'gambar' => $item->gambar ? asset('storage/' . $item->gambar) : null, // Menampilkan URL gambar
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            // Jika tidak ada hasil ditemukan
            if ($formattedHadiah->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No results found',
                    'data' => []
                ]);
            }

            // Kembalikan response dengan hasil filter dan metadata tambahan
            return response()->json([
                'success' => true,
                'message' => 'Data fetched successfully',
                'total' => $formattedHadiah->count(),
                'data' => $formattedHadiah
            ]);
        } catch (\Exception $e) {
            // Menangani kasus error lainnya
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateJumlahKeluar($id, Request $request)
    {
        \Log::info('Memproses updateJumlahKeluar untuk ID: ' . $id);
    
        // Validasi input
        $request->validate([
            'jumlah_keluar' => 'required|integer|min:1',
        ]);
    
        $hadiah = Hadiah::find($id);
        if ($hadiah) {
            \Log::info('Jumlah keluar sebelumnya: ' . $hadiah->jumlah_keluar);
    
            // Ambil jumlah_keluar dari inputan request
            $jumlahKeluarInput = $request->input('jumlah_keluar');
    
            // Update jumlah_keluar sesuai inputan
            $hadiah->jumlah_keluar = $hadiah->jumlah_keluar + $jumlahKeluarInput;
            $hadiah->save();
    
            \Log::info('Jumlah keluar setelah update: ' . $hadiah->jumlah_keluar);
    
            return response()->json(['success' => true, 'message' => 'Jumlah keluar hadiah updated successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Hadiah not found'], 404);
    }
    
    
    
}
