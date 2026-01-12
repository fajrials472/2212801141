<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestGabung;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DosenRequestController extends Controller
{
    /**
     * Menyimpan permintaan gabung kelas dari Dosen.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas_ids' => 'required|array|min:2', // Minimal 2 kelas untuk digabung
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $dosen = Auth::user()->dosen;

        // Cek apakah sudah ada request pending untuk matkul ini
        $existingRequest = RequestGabung::where('dosen_id', $dosen->id)
            ->where('mata_kuliah_id', $request->mata_kuliah_id)
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return back()->with('error', 'Anda sudah memiliki permintaan pending untuk mata kuliah ini.');
        }

        // Simpan ke database
        RequestGabung::create([
            'dosen_id' => $dosen->id,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'kelas_ids' => $request->kelas_ids, // Otomatis jadi JSON karena cast di Model
            'status' => 'pending'
        ]);

        return back()->with('success', 'Permintaan penggabungan kelas berhasil dikirim ke Admin.');
    }
}
