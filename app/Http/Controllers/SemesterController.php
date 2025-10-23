<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;

class SemesterController extends Controller
{
    /**
     * Menaikkan semua data semester di tabel kelas.
     */
    public function naikkanSemester()
    {
        // PERBAIKAN: Menghapus batasan 'where('semester', '<=', 8)'
        // Sekarang ini akan menaikkan SEMUA kelas, termasuk Smt 8 -> 9, Smt 9 -> 10, dst.
        Kelas::query()->update([
            'semester' => DB::raw('semester + 1')
        ]);

        return redirect()->route('dashboard')->with('success', 'Semester berhasil dinaikkan untuk semua kelas.');
    }

    /**
     * Menurunkan semua data semester di tabel kelas.
     */
    public function turunkanSemester()
    {
        // Kita tetap biarkan batasan 'where > 1' di sini
        // agar semester 1 tidak menjadi 0 atau negatif.
        Kelas::where('semester', '>', 1)->update([
            'semester' => DB::raw('semester - 1')
        ]);

        return redirect()->route('dashboard')->with('success', 'Semester berhasil diturunkan untuk semua kelas.');
    }
}
