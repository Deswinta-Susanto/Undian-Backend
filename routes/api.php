<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthAdmin;
use App\Http\Controllers\KandidatHadiahUmumController;
use App\Http\Controllers\KandidatHadiahUtamaController;
use App\Http\Controllers\CustomizeHeaderController;
use App\Http\Controllers\KandidatHiddenController;
use App\Http\Controllers\KandidatHadiahHiddenImport;


use App\Http\Controllers\EventController;
use App\Http\Controllers\DoorprizeController;
use App\Http\Controllers\RiwayatPemenangController;
use App\Exports\RiwayatExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\DoorprizeImportController;


Route::post('login', [AuthAdmin::class, 'login']);
Route::post('logout', [AuthAdmin::class, 'logout']);

Route::get('dataAdmin', [AuthAdmin::class, 'getAllAdmins']);
Route::post('register', [AuthAdmin::class, 'register']);   
Route::post('filterAdmins', [AuthAdmin::class, 'filterAdmins']);
Route::delete('admin/{id}', [AuthAdmin::class, 'deleteAdmin']);
Route::put('updateAdmin/{id}', [AuthAdmin::class, 'updateAdmin']);
Route::get('getAdmin/{id}', [AuthAdmin::class, 'getAdminById']);
Route::post('admin/{id}/reset-password', [AuthAdmin::class, 'resetPassword']);

Route::post('add-event', [EventController::class, 'addEvent']);
Route::get('get-event', [EventController::class, 'getAllEvents']);
Route::get('get-event/{id}', [EventController::class, 'getEventById']);
Route::put('edit-event/{id}', [EventController::class, 'editEvent']);
Route::delete('delete-event/{id}', [EventController::class, 'deleteEvent']);
Route::post('filterEvent', [EventController::class, 'filterEvent']);

// Auth routes
Route::prefix('auth')->group(function () {

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
    Route::put('edit-status', [KandidatHadiahUmumController::class, 'editStatus']);
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
    Route::put('edit-status', [KandidatHadiahUtamaController::class, 'editStatus']);
});

Route::prefix('doorprize')->group(function () {
    Route::post('add-hadiah', [DoorprizeController::class, 'store']);
    Route::get('get-hadiah', [DoorprizeController::class, 'index']);
    Route::get('get-hadiah/{id}', [DoorprizeController::class, 'show']);
    Route::put('update/{id}', [DoorprizeController::class, 'update']);
    Route::delete('delete/{id}', [DoorprizeController::class, 'destroy']);
    Route::post('filter-hadiah', [DoorprizeController::class, 'filterByName']);
    Route::put('update-jumlah-keluar/{id}', [DoorprizeController::class, 'updateJumlahKeluar']);
});

Route::prefix('riwayat')->group(function () {

    Route::get('riwayat', [RiwayatPemenangController::class, 'getAllRiwayat']);
    Route::delete('riwayat/{id}', [RiwayatPemenangController::class, 'deleteRiwayat']);
    Route::delete('delete-all', [RiwayatPemenangController::class, 'deleteRiwayatAll']);

});

Route::post('riwayat/add', [RiwayatPemenangController::class, 'addRiwayat']);
Route::get('all-kandidat', [KandidatHadiahUtamaController::class, 'getKandidatForSpinner']);
Route::post('import-doorprizes', [DoorprizeController::class, 'import']);
Route::get('export-riwayat', [RiwayatPemenangController::class, 'export']);




Route::prefix('customize-header')->group(function () {

    Route::post('create', [CustomizeHeaderController::class, 'createCustomizeHeader']);
    Route::get('get', [CustomizeHeaderController::class, 'getAllCustomizeHeaders']);
    Route::get('get/{id}', [CustomizeHeaderController::class, 'getCustomizeHeaderById']);
    Route::put('update/{id}', [CustomizeHeaderController::class, 'updateCustomizeHeader']);
    Route::delete('delete/{id}', [CustomizeHeaderController::class, 'deleteCustomizeHeader']);

});


Route::prefix('kandidat-hadiah-hidden')->group(function () {
    Route::get('/', [KandidatHiddenController::class, 'index']);
    Route::post('/import', [KandidatHiddenController::class, 'importExcel']);
    Route::get('/export', [KandidatHiddenController::class, 'exportToExcel']);
    Route::post('/', [KandidatHiddenController::class, 'store']);
    Route::get('/{id}', [KandidatHiddenController::class, 'show']);
    Route::put('/{id}', [KandidatHiddenController::class, 'update']);
    Route::delete('/{id}', [KandidatHiddenController::class, 'destroy']);
    Route::delete('/', [KandidatHiddenController::class, 'destroyAll']);
});



//Route untuk mendapatkan data pengguna yang terautentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
