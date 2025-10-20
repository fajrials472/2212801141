<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DataExportController extends Controller
{
    /**
     * Menangani download data mahasiswa dalam format PDF.
     */
    public function downloadMahasiswa(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        $angkatan = $request->input('angkatan');

        $query = Mahasiswa::with('user.kelas');

        $judul = "DAFTAR MAHASISWA";
        $subJudul = [];

        if ($kelasId && $kelasId !== 'semua') {
            $query->whereHas('user.kelas', fn($q) => $q->where('id', $kelasId));
            $kelas = Kelas::find($kelasId);
            $subJudul[] = "Kelas: " . ($kelas->nama_kelas ?? 'N/A');
        }

        if ($angkatan && $angkatan !== 'semua') {
            $query->where('angkatan', $angkatan);
            $subJudul[] = "Angkatan: " . $angkatan;
        }

        $data = $query->get();
        $kolom = ['NIM' => 'nim', 'Nama' => 'nama_mahasiswa', 'Kelas' => 'user.kelas.nama_kelas', 'Alamat' => 'alamat'];
        $tanggalSekarang = now()->locale('id')->isoFormat('D MMMM YYYY');

        $pdf = Pdf::loadView('admin.data_export', compact('judul', 'subJudul', 'data', 'kolom', 'tanggalSekarang'));
        return $pdf->stream('data-mahasiswa.pdf');
    }

    /**
     * Menangani download data dosen dalam format PDF.
     */
    public function downloadDosen(Request $request)
    {
        $prodiId = $request->input('prodi_id');

        $query = Dosen::with('prodi');

        $judul = "DAFTAR DOSEN";
        $subJudul = [];

        if ($prodiId && $prodiId !== 'semua') {
            $query->whereHas('prodi', fn($q) => $q->where('prodi.id', $prodiId));
            $prodi = Prodi::find($prodiId);
            $subJudul[] = "Program Studi: " . ($prodi->nama_prodi ?? 'N/A');
        }

        $data = $query->get();
        $kolom = ['NIDN' => 'nidn', 'Nama Dosen' => 'nama_dosen', 'Program Studi' => 'prodi_list', 'Email' => 'email'];
        $tanggalSekarang = now()->locale('id')->isoFormat('D MMMM YYYY');

        $pdf = Pdf::loadView('admin.data_export', compact('judul', 'subJudul', 'data', 'kolom', 'tanggalSekarang'));
        return $pdf->stream('data-dosen.pdf');
    }
}
