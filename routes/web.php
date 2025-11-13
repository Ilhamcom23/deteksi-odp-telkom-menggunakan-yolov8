<?php

use App\Http\Controllers\OdpDetectionController;
use Illuminate\Support\Facades\Route;

// Halaman utama dashboard ODP
Route::get('/', [OdpDetectionController::class, 'index'])->name('odp.index');


Route::get('/create', [OdpDetectionController::class, 'create'])->name('odp.create');


Route::post('/store', [OdpDetectionController::class, 'store'])->name('odp.store');


Route::get('/realtime', [OdpDetectionController::class, 'realtime'])->name('odp.realtime');


Route::get('/test-log', function () {
    \Log::info('Tes logging setelah file dihapus!');
    return 'Log berhasil dibuat ulang.';
});


Route::get('/odp/export/excel', [OdpDetectionController::class, 'exportExcel'])
    ->name('odp.export.excel');
Route::get('/export-excel', [OdpDetectionController::class, 'exportExcel'])->name('odp.export');
