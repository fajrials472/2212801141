<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Services\JadwalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalController extends Controller
{
    protected $jadwalService;

    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    public function index(Request $request)
    {
        $query = Jadwal::query();
        $jenisSemester = $request->input('jenis_semester');
        $prodiId = $request->input('prodi_id');
        $kelasId = $request->input('kelas_id');

        if ($jenisSemester) {
            $query->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
            });
        }
        if ($prodiId) {
            $query->whereHas('penugasan.kelas.prodi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            });
        }
        if ($kelasId) {
            $query->whereHas('penugasan.kelas', function ($q) use ($kelasId) {
                $q->where('id', $kelasId);
            });
        }

        $jadwal = $query->with(['penugasan.mataKuliah', 'penugasan.dosen', 'penugasan.kelas.prodi', 'ruangan'])
            ->get()
            ->groupBy('penugasan.kelas.nama_kelas');

        $allProdi = Prodi::orderBy('nama_prodi')->get();
        $allKelas = Kelas::orderBy('nama_kelas')->get();

        // PERBAIKAN: Mengambil daftar arsip jadwal dengan pagination
        $arsipJadwal = DB::table('jadwal_versions')->orderBy('created_at', 'desc')->paginate(5);

        return view('jadwal', compact('jadwal', 'jenisSemester', 'allProdi', 'prodiId', 'allKelas', 'kelasId', 'arsipJadwal'));
    }

    public function generate()
    {
        // Method generate Anda memanggil service yang sudah diupdate
        $this->jadwalService->generateJadwal();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal baru berhasil dibuat dan jadwal sebelumnya telah diarsipkan.');
    }

    /**
     * Menampilkan detail dari sebuah arsip jadwal.
     */
    public function lihatArsip($versionId)
    {
        $versi = DB::table('jadwal_versions')->find($versionId);
        if (!$versi) {
            abort(404, 'Versi jadwal tidak ditemukan.');
        }

        $arsipData = DB::table('arsip_jadwal')
            ->where('jadwal_version_id', $versionId)
            ->join('penugasan', 'arsip_jadwal.penugasan_id', '=', 'penugasan.id')
            ->join('mata_kuliah', 'penugasan.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->join('kelas', 'penugasan.kelas_id', '=', 'kelas.id')
            ->join('dosen', 'penugasan.dosen_id', '=', 'dosen.id')
            ->join('ruangan', 'arsip_jadwal.ruangan_id', '=', 'ruangan.id')
            ->select(
                'arsip_jadwal.hari',
                'arsip_jadwal.jam_mulai',
                'arsip_jadwal.jam_selesai',
                'mata_kuliah.nama_mk',
                'mata_kuliah.semester',
                'kelas.nama_kelas',
                'dosen.nama_dosen',
                'ruangan.nama_ruangan'
            )->get();

        $jadwal = $arsipData->groupBy('nama_kelas');

        return view('arsip_jadwal_show', compact('jadwal', 'versi'));
    }

    /**
     * Menghapus sebuah arsip jadwal.
     */
    public function hapusArsip($versionId)
    {
        // onDelete('cascade') di migrasi akan otomatis menghapus semua jadwal terkait
        DB::table('jadwal_versions')->where('id', $versionId)->delete();

        return redirect()->route('jadwal.index')->with('success', 'Arsip jadwal berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        // 1. Ambil query filter dari URL (sama seperti di method index)
        $query = Jadwal::query();
        $jenisSemester = $request->input('jenis_semester');
        $prodiId = $request->input('prodi_id');

        // 2. Terapkan filter semester
        if ($jenisSemester) {
            $query->whereHas('penugasan.mataKuliah', function ($q) use ($jenisSemester) {
                if ($jenisSemester === 'gasal') $q->whereRaw('semester % 2 = 1');
                elseif ($jenisSemester === 'genap') $q->whereRaw('semester % 2 = 0');
            });
        }

        // 3. Terapkan filter prodi
        if ($prodiId) {
            $query->whereHas('penugasan.kelas.prodi', function ($q) use ($prodiId) {
                $q->where('id', $prodiId);
            });
        }

        // 4. Ambil data prodi untuk ditampilkan di header
        $prodi = $prodiId ? Prodi::find($prodiId) : null;

        // 5. Ambil data jadwal dan kelompokkan berdasarkan kelas
        $jadwalPerKelas = $query->with([
            'penugasan.mataKuliah',
            'penugasan.dosen',
            'penugasan.kelas.prodi', // Pastikan relasi ini ada
            'ruangan'
        ])
            ->get()
            ->sortBy('penugasan.kelas.nama_kelas') // Urutkan berdasarkan nama kelas
            ->groupBy('penugasan.kelas.nama_kelas');

        // 6. Siapkan data tanggal untuk dicetak
        $tanggalSekarang = Carbon::now()->translatedFormat('d F Y');

        // 7. Kirim semua data ke view cetak yang baru
        return view('jadwal_cetak', compact('jadwalPerKelas', 'prodi', 'tanggalSekarang'));
    }
}
