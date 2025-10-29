<?php

namespace App\Services;

use App\Models\Ruangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalService
{
    // ... (properti dan constructor Anda tetap sama) ...
    private $jamKuliah;
    private $hariKerjaNormal;
    private $hariKuliahKelasF;
    private $durasiSks;
    private $maxCoursesPerDay;
    private $minDosenDaysPerWeek;

    public function __construct()
    {
        $this->jamKuliah = ['Senin'  => ['08:00', '17:00'], 'Selasa' => ['08:00', '17:00'], 'Rabu'   => ['08:00', '17:00'], 'Kamis'  => ['08:00', '17:00'], 'Jumat'  => ['08:00', '17:00'], 'Sabtu'  => ['08:00', '17:00'], 'Minggu' => ['08:00', '17:00'],];
        $this->hariKerjaNormal   = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $this->hariKuliahKelasF  = ['Sabtu', 'Minggu'];
        $this->durasiSks         = [1 => 60, 2 => 90, 3 => 120];
        $this->maxCoursesPerDay = ['A' => 2, 'B' => 2, 'C' => 2, 'D' => 2, 'E' => 2, 'F' => 4,];
        $this->minDosenDaysPerWeek = 4;
    }


    // PERBAIKAN: Method generateJadwal sekarang menerima parameter
    public function generateJadwal($tahunAjaran, $jenisSemester)
    {
        // 1. Dapatkan ID penugasan yang relevan untuk periode ini (HANYA UNTUK YANG AKAN DI-GENERATE)
        $penugasanIdsQuery = DB::table('penugasan')
            ->join('kelas', 'penugasan.kelas_id', '=', 'kelas.id')
            ->join('mata_kuliah', 'penugasan.mata_kuliah_id', '=', 'mata_kuliah.id');

        // Filter HANYA berdasarkan Jenis Semester (Gasal/Genap)
        if ($jenisSemester === 'gasal') {
            $penugasanIdsQuery->whereRaw('mata_kuliah.semester % 2 = 1');
        } elseif ($jenisSemester === 'genap') {
            $penugasanIdsQuery->whereRaw('mata_kuliah.semester % 2 = 0');
        }

        $penugasanIds = $penugasanIdsQuery->pluck('penugasan.id');

        // ====================================================================
        // MODIFIKASI INTI: Arsipkan dan Hapus SEMUA jadwal yang ada
        // ====================================================================

        // Ambil SEMUA jadwal aktif saat ini untuk diarsipkan
        $jadwalAktif = DB::table('jadwal')->get();

        if ($jadwalAktif->isNotEmpty()) {
            // Buat versi arsip baru
            $namaVersi = 'Arsip Jadwal ' . $tahunAjaran . ' (' . ucfirst($jenisSemester) . ') - ' . now()->format('d M Y');
            $versionId = DB::table('jadwal_versions')->insertGetId([
                'nama_versi' => $namaVersi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Masukkan SEMUA jadwal yang baru diambil ke arsip
            $jadwalUntukArsip = $jadwalAktif->map(fn($item) => [
                'jadwal_version_id' => $versionId,
                'penugasan_id' => $item->penugasan_id,
                'ruangan_id' => $item->ruangan_id,
                'hari' => $item->hari,
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            DB::table('arsip_jadwal')->insert($jadwalUntukArsip);

            // HAPUS SEMUA data dari tabel 'jadwal' (Genap & Gasal)
            // Menggunakan truncate untuk reset total tabel, atau delete()
            DB::table('jadwal')->truncate();
        }
        // ====================================================================
        // AKHIR MODIFIKASI

        // 3. Ambil data penugasan untuk di-generate (HANYA YANG RELEVAN)
        $penugasan = DB::table('penugasan')
            ->join('mata_kuliah', 'penugasan.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->join('kelas', 'penugasan.kelas_id', '=', 'kelas.id')
            ->join('dosen', 'penugasan.dosen_id', '=', 'dosen.id')
            ->whereIn('penugasan.id', $penugasanIds) // Filter utama di sini
            ->select('penugasan.*', 'mata_kuliah.sks', 'kelas.nama_kelas', 'dosen.nama_dosen', 'mata_kuliah.nama_mk')
            ->orderBy('mata_kuliah.sks', 'desc')
            ->get();

        set_time_limit(300);

        // ... (Sisa logika perulangan 'while' Anda tetap sama, dan akan mengisi tabel 'jadwal' yang sekarang kosong dengan jadwal yang baru) ...
        $ruangan = Ruangan::all();
        $jadwalDosen = [];
        $jadwalRuangan = [];
        $jadwalKelas = [];
        $scheduledAssignments = [];
        $scheduledCoursesPerDay = [];
        $dosenDays = [];
        $assignmentQueue = $penugasan->all();

        while (!empty($assignmentQueue)) {
            $currentQueue = [];
            foreach ($assignmentQueue as $item) {
                $uniqueAssignmentKey = $item->mata_kuliah_id . '-' . $item->kelas_id;
                if (in_array($uniqueAssignmentKey, $scheduledAssignments, true)) continue;

                $durasiMenit = $this->durasiSks[$item->sks] ?? 90;
                $isScheduled = false;
                $tipeKelas = strtoupper(substr($item->nama_kelas, 3, 1));
                $hariKuliah = in_array($tipeKelas, ['A', 'B', 'C', 'D', 'E'], true) ? $this->hariKerjaNormal : $this->hariKuliahKelasF;

                if (!isset($dosenDays[$item->dosen_id])) $dosenDays[$item->dosen_id] = [];

                foreach ($hariKuliah as $hari) {
                    if ($isScheduled) break;
                    $jamMulai = strtotime($this->jamKuliah[$hari][0]);
                    $jamSelesaiHarian = strtotime($this->jamKuliah[$hari][1]);

                    for ($pass = 1; $pass <= 2 && !$isScheduled; $pass++) {
                        $tryPreferNewDayForDosen = ($pass === 1 && count($dosenDays[$item->dosen_id]) < $this->minDosenDaysPerWeek);
                        $jam = $jamMulai;

                        while ($jam < $jamSelesaiHarian) {
                            $jamSelesai = $jam + ($durasiMenit * 60);

                            if (($jam >= strtotime('12:00') && $jam < strtotime('13:00')) || ($jamSelesai > strtotime('12:00') && $jamSelesai <= strtotime('13:00'))) {
                                $jam = strtotime('13:00');
                                continue;
                            }
                            if ($jamSelesai > strtotime('17:30')) break;

                            $countHariIni = $scheduledCoursesPerDay[$item->kelas_id][$hari] ?? 0;
                            $limitPerHari = $this->maxCoursesPerDay[$tipeKelas] ?? 2;
                            if ($countHariIni >= $limitPerHari) break;

                            if ($tryPreferNewDayForDosen && in_array($hari, $dosenDays[$item->dosen_id], true)) {
                                $jam += 1800;
                                continue;
                            }

                            foreach ($ruangan as $ruang) {
                                if ($this->isSlotTersedia($hari, $jam, $jamSelesai, $item->dosen_id, $ruang->id, $item->kelas_id, $jadwalDosen, $jadwalRuangan, $jadwalKelas)) {
                                    DB::table('jadwal')->insert([
                                        'penugasan_id' => $item->id,
                                        'mata_kuliah_id' => $item->mata_kuliah_id,
                                        'dosen_id' => $item->dosen_id,
                                        'kelas_id' => $item->kelas_id,
                                        'ruangan_id' => $ruang->id,
                                        'hari' => $hari,
                                        'jam_mulai' => date('H:i:s', $jam),
                                        'jam_selesai' => date('H:i:s', $jamSelesai),
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                    $jadwalDosen[$item->dosen_id][] = ['hari' => $hari, 'mulai' => $jam, 'selesai' => $jamSelesai];
                                    $jadwalRuangan[$ruang->id][] = ['hari' => $hari, 'mulai' => $jam, 'selesai' => $jamSelesai];
                                    $jadwalKelas[$item->kelas_id][$hari][] = ['mulai' => $jam, 'selesai' => $jamSelesai];
                                    $scheduledCoursesPerDay[$item->kelas_id][$hari] = ($scheduledCoursesPerDay[$item->kelas_id][$hari] ?? 0) + 1;
                                    if (!in_array($hari, $dosenDays[$item->dosen_id], true)) {
                                        $dosenDays[$item->dosen_id][] = $hari;
                                    }

                                    $scheduledAssignments[] = $uniqueAssignmentKey;
                                    $isScheduled = true;
                                    break;
                                }
                            }
                            if ($isScheduled) break;
                            $jam += 1800;
                        }
                    }
                }
                if (!$isScheduled) $currentQueue[] = $item;
            }
            if (empty($currentQueue) || count($currentQueue) === count($assignmentQueue)) break;
            $assignmentQueue = $currentQueue;
        }

        if (!empty($assignmentQueue)) Log::info('Penugasan yang gagal dijadwalkan (sisa):', $assignmentQueue);
        return true;
    }

    private function isSlotTersedia($hari, $mulai, $selesai, $dosenId, $ruanganId, $kelasId, $jadwalDosen, $jadwalRuangan, $jadwalKelas)
    {
        if (isset($jadwalKelas[$kelasId][$hari])) {
            foreach ($jadwalKelas[$kelasId][$hari] as $jadwal) if (max($mulai, $jadwal['mulai']) < min($selesai, $jadwal['selesai'])) return false;
        }
        if (isset($jadwalDosen[$dosenId])) {
            foreach ($jadwalDosen[$dosenId] as $jadwal) if ($jadwal['hari'] == $hari && max($mulai, $jadwal['mulai']) < min($selesai, $jadwal['selesai'])) return false;
        }
        if (isset($jadwalRuangan[$ruanganId])) {
            foreach ($jadwalRuangan[$ruanganId] as $jadwal) if ($jadwal['hari'] == $hari && max($mulai, $jadwal['mulai']) < min($selesai, $jadwal['selesai'])) return false;
        }
        return true;
    }
}
