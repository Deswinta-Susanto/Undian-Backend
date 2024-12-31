<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthAdmin extends Controller
{

    public function register(Request $request)
    {
        // Mengambil data admin yang sedang terautentikasi menggunakan guard 'api'
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
    
        try {
            // Validasi input dari request
            $validatedData = $request->validate([
                'niip' => 'required|string|unique:admins',
                'nama' => 'required|string|max:255',
                'unit' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);
            
            // Menambahkan role yang diterima dari input atau default 'General'
            $validatedData['role'] = $request->input('role', 'General');
            
            // Mengenkripsi password sebelum menyimpannya
            $validatedData['password'] = Hash::make($validatedData['password']);
            
            // Membuat admin baru
            $admin = Admin::create($validatedData);
            
            // Membuat token JWT untuk admin baru
            $token = JWTAuth::fromUser($admin);
            
            return response()->json([
                'message' => 'Admin registered successfully',
                'admin' => $admin,
            ], 201);
        } catch (ValidationException $e) {
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
        $validatedData = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

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

    // Cari user berdasarkan 'nama'
    $user = Admin::where('nama', $request->nama)->first();

    // Jika user tidak ditemukan
    if (!$user) {
        return response()->json(['error' => 'Unauthorized: User not found'], 401);
    }
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Unauthorized: Incorrect password'], 401);
    }
    
    // Buat token JWT
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'data' => $user,
    ]);
}

public function getAllAdmins(Request $request)
{
    $admin = Auth::guard('api')->user();

    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid token',
        ], 401);
    }

    $search = $request->query('q');

    if ($search) {
        try {
            $admins = Admin::where('nama', 'like', "%$search%")->get();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data',
                'error' => $e->getMessage(),
            ], 500);
        }
    } else {
        $admins = Admin::all();
    }

    return response()->json([
        'success' => true,
        'message' => 'Admin authenticated successfully',
        'data' => $admins,
    ]);
}
    
    public function deleteAdmin($id)
{
    $admin = Auth::guard('api')->user();  
    
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid token',
        ], 401);
    }

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
    $admin = Auth::guard('api')->user();  
    
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid token',
        ], 401);
    }
    
    try {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $validatedData = $request->validate([
            'niip' => 'nullable|string|unique:admins,niip,' . $id, 
            'nama' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
        ]);

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
        $admin = Auth::guard('api')->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }

        $adminData = Admin::find($id);

        if (!$adminData) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        return response()->json($adminData, 200);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token has expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['error' => 'Token is missing'], 401);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server Error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function checkToken()
{
    try {
        $user = auth()->user();
        return response()->json(['user' => $user]);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token is expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['error' => 'Token is absent'], 401);
    }
}

public function logout(Request $request)
{
    try {
        // Mengambil token dari header Authorization
        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 400);
        }

        // Invalidasi token untuk logout
        JWTAuth::invalidate($token);

        return response()->json(['message' => 'Successfully logged out']);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to logout: ' . $e->getMessage(),
        ], 500);
    }
}
}
