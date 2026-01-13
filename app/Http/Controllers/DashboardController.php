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

            // --- 1. LOGIKA UNTUK MODAL GABUNG KELAS ---
            // Kita ambil data mentah dulu untuk debugging lebih mudah
            $semuaPenugasan = DB::table('penugasan')
                ->join('mata_kuliah', 'penugasan.mata_kuliah_id', '=', 'mata_kuliah.id')
                ->join('kelas', 'penugasan.kelas_id', '=', 'kelas.id')
                ->where('penugasan.dosen_id', $dosen->id)
                ->whereNull('penugasan.kode_gabung') // Hanya yang BELUM digabung
                ->select(
                    'penugasan.id as penugasan_id',
                    'mata_kuliah.id as mk_id',
                    'mata_kuliah.nama_mk',
                    'kelas.id as kelas_id',
                    'kelas.nama_kelas'
                )
                ->get();

            // Filter: Group by Mata Kuliah, lalu ambil yang jumlah kelasnya > 1
            $kandidatGabung = $semuaPenugasan->groupBy('mk_id')->filter(function ($items) {
                return $items->count() > 1;
            });

            // --- 2. LOGIKA UNTUK JADWAL UTAMA ---
            $tahunAjaran = $request->input('tahun_ajaran');
            $jenisSemester = $request->input('jenis_semester');

            $jadwalQuery = Jadwal::query()
                ->whereHas('penugasan', function ($query) use ($dosen) {
                    $query->where('dosen_id', $dosen->id);
                })
                ->with(['penugasan.mataKuliah', 'penugasan.kelas', 'ruangan']);

            // Filter Tahun Ajaran
            if ($tahunAjaran) {
                $jadwalQuery->whereHas('penugasan.kelas', function ($q) use ($tahunAjaran) {
                    $q->where('angkatan', $tahunAjaran);
                });
            }

            // Filter Semester (Gasal/Genap)
            if ($jenisSemester) {
                $jadwalQuery->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                    if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                    elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
                });
            }

            $jadwalData = $jadwalQuery->get();

            // Urutkan Hari: Senin -> Minggu
            $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

            $jadwalGroupedByDay = $jadwalData->groupBy('hari')->sortBy(function ($items, $key) use ($dayOrder) {
                return $dayOrder[$key] ?? 99;
            });

            $allTahunAjaran = Kelas::distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

            return view('dosen.dashboard', compact('dosen', 'jadwalGroupedByDay', 'allTahunAjaran', 'tahunAjaran', 'jenisSemester', 'kandidatGabung'));
        }

        // =======================================================
        // LOGIKA UNTUK ROLE MAHASISWA
        // =======================================================
        if ($user->role === 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;
            $jadwalGroupedByDay = collect();
            $mahasiswaKelas = null;
            $jenisSemester = $request->input('jenis_semester');

            if ($mahasiswa && $mahasiswa->kelas()->exists()) {
                $mahasiswaKelas = $mahasiswa->kelas()->first();
                if ($mahasiswaKelas) {
                    $jadwalQuery = Jadwal::query()
                        ->whereHas('penugasan', function ($query) use ($mahasiswaKelas) {
                            $query->where('kelas_id', $mahasiswaKelas->id);
                        });

                    if ($jenisSemester) {
                        $jadwalQuery->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                            if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                            elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
                        });
                    }

                    $jadwalData = $jadwalQuery->with(['penugasan.dosen', 'penugasan.mataKuliah', 'ruangan'])->get();
                    $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

                    // PERBAIKAN LOGIKA SORTING MAHASISWA JUGA
                    $jadwalGroupedByDay = $jadwalData->groupBy('hari')->sortBy(function ($items, $key) use ($dayOrder) {
                        return $dayOrder[$key] ?? 99;
                    });
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

        $requestPending = \App\Models\RequestGabung::with(['dosen', 'mataKuliah'])
            ->where('status', 'pending')
            ->get();

        $data = compact(
            'totalProdi',
            'totalDosen',
            'totalMahasiswa',
            'totalMataKuliah',
            'totalRuangan',
            'totalKelas',
            'allKelas',
            'allAngkatan',
            'allProdi',
            'requestPending' // <--- Masukkan variabel ini ke compact
        );

        return view('dashboard', $data);;
    }
}
