<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan role user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // =======================================================
        // LOGIKA UNTUK ROLE DOSEN
        // =======================================================
        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                return redirect('/')->with('error', 'Data profil dosen tidak ditemukan.');
            }

            $tahunAjaran = $request->input('tahun_ajaran');
            $jenisSemester = $request->input('jenis_semester');

            $jadwalQuery = Jadwal::query()
                ->whereHas('penugasan', function ($query) use ($dosen) {
                    $query->where('dosen_id', $dosen->id);
                })
                ->with(['penugasan.mataKuliah', 'penugasan.kelas', 'ruangan']);

            if ($tahunAjaran) {
                $jadwalQuery->whereHas('penugasan.kelas', function ($q) use ($tahunAjaran) {
                    $q->where('angkatan', $tahunAjaran);
                });
            }

            if ($jenisSemester) {
                $jadwalQuery->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                    if ($jenisSemester === 'gasal') {
                        $q->whereRaw('semester % 2 = 1');
                    } elseif ($jenisSemester === 'genap') {
                        $q->whereRaw('semester % 2 = 0');
                    }
                });
            }

            $jadwalData = $jadwalQuery->get();
            $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

            // PERBAIKAN: Gunakan 'keyBy' untuk menjaga urutan hari
            $jadwalGroupedByDay = $jadwalData->sortBy(function ($jadwal) use ($dayOrder) {
                return $dayOrder[$jadwal->hari] ?? 99;
            })->groupBy('hari')->keyBy(function ($item, $key) use ($dayOrder) {
                return $dayOrder[$key] ?? 99;
            })->sortKeys();

            $allTahunAjaran = Kelas::distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

            return view('dosen.dashboard', compact('dosen', 'jadwalGroupedByDay', 'allTahunAjaran', 'tahunAjaran', 'jenisSemester'));
        }

        // =======================================================
        // LOGIKA UNTUK ROLE MAHASISWA (DIREVISI)
        // =======================================================
        if ($user->role === 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;
            $jadwalGroupedByDay = collect();
            $mahasiswaKelas = null;

            // Mengambil filter jenis semester
            $jenisSemester = $request->input('jenis_semester');

            if ($mahasiswa && $mahasiswa->kelas()->exists()) {
                $mahasiswaKelas = $mahasiswa->kelas()->first();
                if ($mahasiswaKelas) {
                    $jadwalQuery = Jadwal::query()
                        ->whereHas('penugasan', function ($query) use ($mahasiswaKelas) {
                            $query->where('kelas_id', $mahasiswaKelas->id);
                        });

                    // Menerapkan filter Gasal/Genap
                    if ($jenisSemester) {
                        $jadwalQuery->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                            if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                            elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
                        });
                    }

                    $jadwalData = $jadwalQuery->with(['penugasan.dosen', 'penugasan.mataKuliah', 'ruangan'])->get();
                    $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];
                    $jadwalGroupedByDay = $jadwalData->sortBy(fn($j) => $dayOrder[$j->hari] ?? 99)->groupBy('hari');
                }
            }
            return view('mahasiswa.dashboard', compact('user', 'mahasiswaKelas', 'jadwalGroupedByDay', 'jenisSemester'));
        }

        // =======================================================
        // LOGIKA UNTUK ROLE ADMIN (DEFAULT)
        // =======================================================
        $totalProdi = Prodi::count();
        $totalDosen = Dosen::count();
        $totalMahasiswa = Mahasiswa::count();
        $totalMataKuliah = MataKuliah::count();
        $totalRuangan = Ruangan::count();
        $totalKelas = Kelas::count();

        $allKelas = Kelas::orderBy('nama_kelas')->get();
        $allAngkatan = Mahasiswa::distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');
        $allProdi = Prodi::orderBy('nama_prodi')->get();

        $data = compact('totalProdi', 'totalDosen', 'totalMahasiswa', 'totalMataKuliah', 'totalRuangan', 'totalKelas', 'allKelas', 'allAngkatan', 'allProdi');
        return view('dashboard', $data);
    }
}
