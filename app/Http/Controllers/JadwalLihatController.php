<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Prodi;
use App\Models\Kelas;
use Illuminate\Http\Request;

class JadwalLihatController extends Controller
{
    public function index(Request $request)
    {
        $semester = $request->input('semester');

        $prodi = Prodi::with(['kelas' => function ($query) use ($semester) {
            if ($semester) {
                $query->where('semester', $semester);
            }
        }])->get();

        return view('jadwal_lihat_index', compact('prodi', 'semester'));
    }

    public function show(Kelas $kelas, Request $request)
    {
        $tahunAjaran = $request->input('tahun_ajaran');
        $jenisSemester = $request->input('jenis_semester');

        $jadwal = Jadwal::with(['mataKuliah', 'dosen', 'ruangan'])
            ->where('kelas_id', $kelas->id);

        if ($tahunAjaran) {
            $jadwal->where('tahun_ajaran', $tahunAjaran);
        }
        if ($jenisSemester) {
            $jadwal->where('jenis_semester', $jenisSemester);
        }

        $jadwal = $jadwal->get();

        $jadwalGrid = [];
        foreach ($jadwal as $item) {
            $hari = $item->hari;
            $jam_mulai = date('H:i', strtotime($item->jam_mulai));
            $jadwalGrid[$hari][$jam_mulai] = $item;
        }

        return view('jadwal_lihat_show', compact('kelas', 'jadwalGrid', 'tahunAjaran', 'jenisSemester'));
    }
}
