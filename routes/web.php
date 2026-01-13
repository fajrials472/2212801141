<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JadwalCetakController;
use App\Http\Controllers\JadwalLihatController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\UjiBentrokController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Menyertakan rute otentikasi
require __DIR__ . '/auth.php';

// Rute halaman utama
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});


// =====================================================================
// RUTE SETELAH LOGIN
// =====================================================================
Route::middleware(['auth'])->group(function () {

    // Rute dashboard utama untuk semua role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Alias rute dashboard untuk Dosen & Mahasiswa
    Route::get('/dosen/dashboard', [DashboardController::class, 'index'])->name('dosen.dashboard');
    Route::get('/mahasiswa/dashboard', [DashboardController::class, 'index'])->name('mahasiswa.dashboard');

    // Rute umum
    Route::get('/lihat-jadwal', [JadwalLihatController::class, 'index'])->name('jadwal.lihat');
    Route::get('/lihat-jadwal/{kelas}', [JadwalLihatController::class, 'show'])->name('jadwal.lihat.kelas');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // =====================================================================
    // RUTE KHUSUS UNTUK ADMIN
    // =====================================================================
    Route::middleware(['admin'])
        ->prefix('admin')
        // Baris ->name('admin.') telah dihapus dari sini
        ->group(function () {
            Route::resource('prodi', ProdiController::class);
            Route::resource('dosen', DosenController::class)->except(['show']);
            Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);
            Route::resource('mata-kuliah', MataKuliahController::class)->except(['show']);
            Route::resource('ruangan', RuanganController::class)->except(['show']);
            Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);

            Route::post('/mahasiswa/{mahasiswa}/create-user', [MahasiswaController::class, 'createUser'])->name('mahasiswa.createUser');

            // Penugasan & Generate Jadwal
            Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
            Route::post('/penugasan', [PenugasanController::class, 'store'])->name('penugasan.store');
            Route::delete('/penugasan/{id}', [PenugasanController::class, 'destroy'])->name('penugasan.destroy');
            Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
            Route::post('/jadwal/generate', [JadwalController::class, 'generate'])->name('jadwal.generate');
            Route::get('/jadwal/cetak', [JadwalCetakController::class, 'cetakPerProdiSemester'])->name('jadwal.cetak');
            // --- RUTE BARU UNTUK DOWNLOAD DATA ---
            Route::get('/mahasiswa/download', [DataExportController::class, 'downloadMahasiswa'])->name('mahasiswa.download');
            Route::get('/dosen/download', [DataExportController::class, 'downloadDosen'])->name('dosen.download');

            // Rekapitulasi
            Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

            Route::get('/arsip-jadwal/{versionId}', [JadwalController::class, 'lihatArsip'])->name('arsip.jadwal.show');
            Route::delete('/arsip-jadwal/{versionId}', [JadwalController::class, 'hapusArsip'])->name('arsip.jadwal.destroy');

            Route::post('/semester/naikkan', [SemesterController::class, 'naikkanSemester'])->name('semester.naikkan');
            Route::post('/semester/turunkan', [SemesterController::class, 'turunkanSemester'])->name('semester.turunkan');
        });

    // =====================================================================
    // RUTE KHUSUS UNTUK DOSEN
    // =====================================================================
    Route::middleware(['dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        // ... (rute spesifik untuk dosen)
        Route::get('/jadwal/cetak', [JadwalCetakController::class, 'cetakPerDosen'])->name('jadwal.cetak');
        Route::post('/request-gabung', [\App\Http\Controllers\DosenRequestController::class, 'store'])->name('request.gabung');
    });

    // =====================================================================
    // RUTE KHUSUS UNTUK MAHASISWA
    // =====================================================================
    Route::middleware(['mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // ... (rute spesifik untuk mahasiswa)
        Route::get('/jadwal/cetak', [JadwalCetakController::class, 'cetakPerKelas'])->name('jadwal.cetak');
    });

    Route::post('/admin/request-gabung/{id}/approve', [JadwalController::class, 'setujuiGabung'])
        ->name('admin.request.approve');

    Route::get('/uji-bentrok', [UjiBentrokController::class, 'index'])->name('uji.index');
    // Route::post('/uji-bentrok/cek', [UjiBentrokController::class, 'cek'])->name('uji.cek');


    Route::get('/uji-bentrok', [UjiBentrokController::class, 'index'])->name('uji.index');
});
