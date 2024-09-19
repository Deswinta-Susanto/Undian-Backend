<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthAdmin;
use App\Http\Controllers\KandidatHadiahUmumController;
use App\Http\Controllers\KandidatHadiahUtamaController;

use App\Http\Controllers\EventController;
use App\Http\Controllers\DoorprizeController;
use App\Http\Controllers\RiwayatPemenangController;
use App\Exports\RiwayatExport;
use Maatwebsite\Excel\Facades\Excel;

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthAdmin::class, 'register']);
    Route::post('login', [AuthAdmin::class, 'login']);
    Route::get('dataAdmin', [AuthAdmin::class, 'getAllAdmins']);
    Route::post('logout', [AuthAdmin::class, 'logout'])->middleware('auth:api');
    Route::post('filterAdmins', [AuthAdmin::class, 'filterAdmins']);
    Route::delete('/admin/{id}', [AuthAdmin::class, 'deleteAdmin']);
    Route::put('updateAdmin/{id}', [AuthAdmin::class, 'updateAdmin']);
    Route::get('getAdmin/{id}', [AuthAdmin::class, 'getAdminById']);
    Route::post('admin/{id}/reset-password', [AuthAdmin::class, 'resetPassword']);
});

// Kandidat Hadiah Umum routes
Route::prefix('kandidat-hadiah-umum')->group(function () {
    Route::get('get', [KandidatHadiahUmumController::class, 'index']);
    Route::post('post', [KandidatHadiahUmumController::class, 'store']);
    Route::get('get/{id}', [KandidatHadiahUmumController::class, 'show']);
    Route::put('edit/{id}', [KandidatHadiahUmumController::class, 'update']);
    Route::delete('delete/{id}', [KandidatHadiahUmumController::class, 'destroy']);    
    Route::post('import-excel', [KandidatHadiahUmumController::class, 'importExcel']);
    Route::post('filter', [KandidatHadiahUmumController::class, 'filterKandidat']);
    Route::get('export-kandidat', [KandidatHadiahUmumController::class, 'exportToExcel']);
    Route::delete('delete-all', [KandidatHadiahUmumController::class, 'destroyAll']);
    Route::put('edit-status/{id}', [KandidatHadiahUmumController::class, 'editStatus']);

});

Route::prefix('kandidat-hadiah-utama')->group(function () {
    Route::get('get', [KandidatHadiahUtamaController::class, 'index']);
    Route::post('import', [KandidatHadiahUtamaController::class, 'importExcel']);
    Route::post('/store', [KandidatHadiahUtamaController::class, 'store']);
    Route::get('get/{id}', [KandidatHadiahUtamaController::class, 'show']);
    Route::put('update/{id}', [KandidatHadiahUtamaController::class, 'update']);
    Route::delete('delete/{id}', [KandidatHadiahUtamaController::class, 'destroy']);
    Route::post('filter', [KandidatHadiahUtamaController::class, 'filterKandidat']);
    Route::get('export-kandidat', [KandidatHadiahUtamaController::class, 'exportToExcel']);
    Route::delete('delete-all', [KandidatHadiahUtamaController::class, 'destroyAll']);
    Route::put('update-status/{id}', [KandidatHadiahUtamaController::class, 'updateStatus']);
});

Route::prefix('event')->group(function () {
    Route::post('add-event', [EventController::class, 'addEvent']);
    Route::get('get-event', [EventController::class, 'getAllEvents']);
    Route::get('get-event/{id}', [EventController::class, 'getEventById']);
    Route::put('edit-event/{id}', [EventController::class, 'editEvent']);
    Route::delete('delete-event/{id}', [EventController::class, 'deleteEvent']);
    Route::post('filterEvent', [EventController::class, 'filterEvent']);

});
Route::prefix('doorprize')->group(function () {
    Route::post('add-hadiah', [DoorprizeController::class, 'store']);
    Route::get('get-hadiah', [DoorprizeController::class, 'index']);
    Route::get('get-hadiah/{id}', [DoorprizeController::class, 'show']);
    Route::put('update/{id}', [DoorprizeController::class, 'update']);
    Route::delete('delete/{id}', [DoorprizeController::class, 'destroy']);
    Route::post('filter-hadiah', [DoorprizeController::class, 'filterHadiah']);
    Route::put('update-jumlah-keluar/{id}', [DoorprizeController::class, 'updateJumlahKeluar']);
});

Route::prefix('riwayat')->group(function () {

    Route::get('riwayat', [RiwayatPemenangController::class, 'getAllRiwayat']);
    Route::delete('riwayat/{id}', [RiwayatPemenangController::class, 'deleteRiwayat']);
    Route::delete('delete-all', [RiwayatPemenangController::class, 'deleteRiwayatAll']);

});

Route::post('riwayat/add', [RiwayatPemenangController::class, 'addRiwayat']);

// Route untuk mendapatkan nama kandidat utama
Route::get('all-kandidat', [KandidatHadiahUtamaController::class, 'getKandidatForSpinner']);


Route::get('export-riwayat', [RiwayatPemenangController::class, 'export']);




// Route untuk mendapatkan data pengguna yang terautentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
