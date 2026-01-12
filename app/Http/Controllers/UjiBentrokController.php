<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;

class UjiBentrokController extends Controller
{
    public function index()
    {
        // 1. Definisikan Range Jam (07:00 - 17:00 saja)
        // Karena jadwal maks 17:30, maka slot jam 17:00 adalah slot terakhir.
        $jamList = [];
        for ($i = 7; $i <= 17; $i++) {
            $jamList[] = sprintf("%02d:00", $i);
        }
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // 2. Siapkan Matrix Kosong
        $matrix = [];
        foreach ($hariList as $h) {
            foreach ($jamList as $j) {
                $matrix[$h][$j] = [];
            }
        }

        // 3. Ambil Data
        $semuaJadwal = Jadwal::with(['penugasan.mataKuliah', 'penugasan.kelas', 'penugasan.dosen', 'ruangan'])->get();

        // 4. Mapping Data ke Matrix
        foreach ($semuaJadwal as $jadwal) {
            $hariDb = $jadwal->hari;

            // Konversi jam ke angka
            $startHour = (int) substr($jadwal->jam_mulai, 0, 2);
            $endHour   = (int) substr($jadwal->jam_selesai, 0, 2);
            $endMin    = (int) substr($jadwal->jam_selesai, 3, 2);

            // Jika lewat menit 00, hitung jam tersebut masuk
            if ($endMin > 0) {
                $endHour++;
            }

            // Batasi agar tidak error jika ada jadwal malam di DB (Safety)
            if ($endHour > 18) $endHour = 18;

            // Masukkan ke setiap slot jam
            for ($jam = $startHour; $jam < $endHour; $jam++) {
                // Hanya masukkan jika jam tersebut ada di list (07-17)
                if ($jam <= 17) {
                    $jamFormat = sprintf("%02d:00", $jam);
                    if (isset($matrix[$hariDb][$jamFormat])) {
                        $matrix[$hariDb][$jamFormat][] = $jadwal;
                    }
                }
            }
        }

        return view('uji_matrix', compact('hariList', 'jamList', 'matrix'));
    }
}
