<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthAdmin extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'niip' => 'required|string|unique:admins',
                'nama' => 'required|string|max:255',
            
                'unit' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);
            // Jika 'role' tidak diisi, set default menjadi 'general'
            $validatedData['role'] = $request->input('role', 'General');
            $validatedData['password'] = Hash::make($validatedData['password']);
            $admin = Admin::create($validatedData);
            $token = JWTAuth::fromUser($admin);
            return response()->json([
                'message' => 'Admin registered successfully',
                'admin' => $admin,
                'token' => $token,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request, $id)
{
    try {
        // Validasi input password baru
        $validatedData = $request->validate([
            'password' => 'required|string|min:6|confirmed', // Pastikan ada password_confirmation
        ]);

        // Cari admin berdasarkan ID
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        // Hash password baru
        $admin->password = Hash::make($validatedData['password']);
        $admin->save();

        return response()->json(['message' => 'Password reset successfully'], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'Validation Error',
            'messages' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}    
    public function login(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $admin = Admin::where('nama', $request->input('nama'))->first();
    
        if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Generate token JWT
        $token = JWTAuth::fromUser($admin);
    
        // Response dengan token dan informasi user (role, nama)
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $admin->id,
                'nama' => $admin->nama,
                'role' => $admin->role, // Misal admin punya field 'role'
            ]
        ]);
    }
    

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function getAllAdmins()
    {
        try {
            $totalAdmins = Admin::select('id')->get()->count();
            $dataAdmin = Admin::all();

            return response()->json([
                'total_id_sum' => $totalAdmins,
                'admins' => $dataAdmin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function filterAdmins(Request $request)
    {
        try {
            // Validasi parameter pencarian dari request body
            $validatedData = $request->validate([
                'search' => 'nullable|string',
            ]);
            $search = $validatedData['search'] ?? null;
            $query = Admin::query();
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%$search%")
                      ->orWhere('role', 'like', "%$search%")
                      ->orWhere('niip', 'like', "%$search%");
                });
            }
            $dataAdmin = $query->get();
    
            return response()->json([
                'admins' => $dataAdmin,   
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    

    public function deleteAdmin($id)
{
    try {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}   

public function updateAdmin(Request $request, $id)
{
    try {
        // Cari admin berdasarkan ID
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        // Validasi data yang dikirimkan
        $validatedData = $request->validate([
            'niip' => 'nullable|string|unique:admins,niip,' . $id, // 'unique' kecuali untuk admin dengan ID ini
            'nama' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
        ]);

        // Update data admin
        $admin->update($validatedData);

        return response()->json([
            'message' => 'Admin updated successfully',
            'admin' => $admin,
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'Validation Error',
            'messages' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function getAdminById($id)
{
    try {
        // Cari admin berdasarkan ID
        $admin = Admin::find($id);

        // Jika admin tidak ditemukan
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        // Jika admin ditemukan, kembalikan data admin
        return response()->json($admin, 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}



}
