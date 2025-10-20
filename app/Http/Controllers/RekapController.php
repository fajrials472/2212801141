<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Prodi;
use App\Models\Kelas;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = $request->input('tahun_ajaran');
        $jenisSemester = $request->input('jenis_semester');
        $prodiId = $request->input('prodi_id');
        $kelasId = $request->input('kelas_id');

        $query = Jadwal::with(['mataKuliah', 'dosen', 'ruangan', 'kelas']);

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }
        if ($jenisSemester) {
            $query->where('jenis_semester', $jenisSemester);
        }
        if ($prodiId) {
            $query->whereHas('kelas', function ($q) use ($prodiId) {
                $q->where('prodi_id', $prodiId);
            });
        }
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        // Perbaikan: Mengurutkan berdasarkan hari dan jam
        $rekapData = $query->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get();

        $allProdi = Prodi::all();
        $allKelas = Kelas::all();

        return view('rekap', compact('rekapData', 'allProdi', 'allKelas', 'tahunAjaran', 'jenisSemester', 'prodiId', 'kelasId'));
    }
}
