<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class JadwalCetakController extends Controller
{
    /**
     * Menangani permintaan cetak jadwal dari Admin.
     */
    public function cetakPerProdiSemester(Request $request)
    {
        $prodiId = $request->input('prodi_id');
        $jenisSemester = $request->input('jenis_semester');
        $prodi = $prodiId ? Prodi::find($prodiId) : null;

        $query = Jadwal::query();

        if ($prodiId) {
            $query->whereHas('penugasan.kelas.prodi', fn($q) => $q->where('id', $prodiId));
        }
        if ($jenisSemester) {
            $query->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
            });
        }

        $jadwalPerKelas = $query->with(['penugasan.mataKuliah', 'penugasan.dosen', 'penugasan.kelas.prodi', 'ruangan'])->get()->groupBy('penugasan.kelas.nama_kelas');
        $judul = "Jadwal Belajar Fakultas Teknik";
        $tanggalSekarang = now()->locale('id')->isoFormat('D MMMM YYYY');

        $pdf = Pdf::loadView('jadwal_cetak', compact('jadwalPerKelas', 'prodi', 'jenisSemester', 'judul', 'tanggalSekarang'));

        return $pdf->stream('jadwal-prodi-semester.pdf');
    }

    /**
     * Menangani permintaan cetak jadwal dari Dosen.
     */
    public function cetakPerDosen()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(404, 'Profil dosen tidak ditemukan.');
        }

        $jadwal = Jadwal::whereHas('penugasan', fn($q) => $q->where('dosen_id', $dosen->id))->get();

        // PERBAIKAN: Diubah agar tidak dikelompokkan per kelas
        $jadwalPerKelas = collect(['Semua Kelas' => $jadwal]);
        $judul = "Jadwal Mengajar Dosen";
        $tanggalSekarang = now()->locale('id')->isoFormat('D MMMM YYYY');

        // PERBAIKAN: Tambahkan variabel yang hilang
        $prodi = null;
        $jenisSemester = null;

        $pdf = Pdf::loadView('jadwal_cetak', compact('jadwalPerKelas', 'judul', 'dosen', 'tanggalSekarang', 'prodi', 'jenisSemester'));
        return $pdf->stream('jadwal-mengajar-dosen.pdf');
    }

    /**
     * Menangani permintaan cetak jadwal dari Mahasiswa.
     */
    public function cetakPerKelas()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(404, 'Profil mahasiswa tidak ditemukan.');
        }

        $kelas = $mahasiswa->kelas()->first();
        if (!$kelas) {
            return response('Anda belum terdaftar di kelas manapun.', 404);
        }

        $jadwal = Jadwal::whereHas('penugasan', fn($q) => $q->where('kelas_id', $kelas->id))->get();
        $jadwalPerKelas = collect([$kelas->nama_kelas => $jadwal]);
        $judul = "Jadwal Kuliah Kelas";
        $tanggalSekarang = now()->locale('id')->isoFormat('D MMMM YYYY');

        // PERBAIKAN: Tambahkan variabel yang hilang
        $prodi = $kelas->prodi; // Ambil prodi dari kelas mahasiswa
        $jenisSemester = null;

        $pdf = Pdf::loadView('jadwal_cetak', compact('jadwalPerKelas', 'judul', 'kelas', 'tanggalSekarang', 'prodi', 'jenisSemester'));
        return $pdf->stream('jadwal-kuliah-kelas.pdf');
    }
}
