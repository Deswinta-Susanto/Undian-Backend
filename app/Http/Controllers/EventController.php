<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function addEvent(Request $request)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'nama_event' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date_format:Y-m-d',  
            'tanggal_selesai' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingEvent = Event::where('nama_event', $request->nama_event)
                              ->where('tanggal_mulai', $request->tanggal_mulai)
                              ->first();

        if ($existingEvent) {
            return response()->json([
                'success' => false,
                'message' => 'Event dengan nama dan tanggal yang sama sudah ada!',
            ], 409);
        }

        $event = Event::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil ditambahkan!',
            'data' => [
                'event' => $event,
                'status' => $event->status,
            ]
        ], 201);
    }

    public function getAllEvents(Request $request)
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
        
        // Mencari events berdasarkan query jika ada
        if ($query) {
            $events = Event::where('nama_event', 'like', '%' . $query . '%')->get();
        } else {
            // Ambil semua event jika tidak ada query
            $events = Event::all();
        }
    
        // Menampilkan data dalam response
        return response()->json([
            'success' => true,
            'data' => $events->map(function ($event) {
                return [
                    'event' => $event,
                    'status' => $event->status,
                ];
            })
        ], 200);
    }
    
    public function getEventById($id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }

        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'event' => $event,
                'status' => $event->status,
            ]
        ], 200);
    }

    public function editEvent(Request $request, $id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }

        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan!',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_event' => 'sometimes|string|max:255',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $event->update($request->only('nama_event', 'tanggal_mulai', 'tanggal_selesai'));

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil diperbarui!',
            'data' => [
                'event' => $event,
                'status' => $event->status,
            ],
        ], 200);
    }

    public function deleteEvent($id)
    {
        $admin = Auth::guard('api')->user(); 
        
        // Memeriksa apakah admin terautentikasi
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token',
            ], 401);
        }
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan!',
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus!',
        ], 200);
    }

    // public function filterEvent(Request $request)
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
    //         $validator = Validator::make($request->all(), [
    //             'nama_event' => 'nullable|string|max:255',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'errors' => $validator->errors(),
    //             ], 422);
    //         }

    //         $nama_event = $request->input('nama_event');
    //         $query = Event::query();

    //         if ($nama_event) {
    //             $query->where('nama_event', 'like', "%$nama_event%");
    //         }

    //         $events = $query->get();

    //         \Log::info('Filter results', ['data' => $events]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $events,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         \Log::error('Error during event filter: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error occurred',
    //         ], 500);
    //     }
    // }
}
