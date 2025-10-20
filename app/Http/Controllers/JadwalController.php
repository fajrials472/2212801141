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

        // PERBAIKAN: Mengambil daftar tahun ajaran untuk form generate
        $allTahunAjaran = Kelas::distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');

        $arsipJadwal = DB::table('jadwal_versions')->orderBy('created_at', 'desc')->paginate(5);

        // PERBAIKAN: Mengirim variabel allTahunAjaran ke view
        return view('jadwal', compact('jadwal', 'jenisSemester', 'allProdi', 'prodiId', 'allKelas', 'kelasId', 'arsipJadwal', 'allTahunAjaran'));
    }

    // PERBAIKAN: Mengubah method generate untuk menerima Request
    public function generate(Request $request)
    {
        // Validasi input dari form generate
        $validated = $request->validate([
            'tahun_ajaran' => 'required|string',
            'jenis_semester' => 'required|in:gasal,genap',
        ]);

        // Panggil service dengan parameter yang sudah divalidasi
        $this->jadwalService->generateJadwal($validated['tahun_ajaran'], $validated['jenis_semester']);

        // Arahkan kembali ke halaman index dengan filter yang sama
        return redirect()->route('jadwal.index', [
            'jenis_semester' => $validated['jenis_semester']
        ])->with('success', 'Jadwal baru berhasil dibuat dan jadwal sebelumnya telah diarsipkan.');
    }

    /**
     * Menampilkan detail dari sebuah arsip jadwal.
     */
    public function lihatArsip($versionId)
    {
        // ... (method ini tidak berubah)
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
            ->select('arsip_jadwal.hari', 'arsip_jadwal.jam_mulai', 'arsip_jadwal.jam_selesai', 'mata_kuliah.nama_mk', 'mata_kuliah.semester', 'kelas.nama_kelas', 'dosen.nama_dosen', 'ruangan.nama_ruangan')
            ->get();

        $jadwal = $arsipData->groupBy('nama_kelas');

        return view('arsip_jadwal_show', compact('jadwal', 'versi'));
    }

    /**
     * Menghapus sebuah arsip jadwal.
     */
    public function hapusArsip($versionId)
    {
        // ... (method ini tidak berubah)
        DB::table('jadwal_versions')->where('id', $versionId)->delete();
        return redirect()->route('jadwal.index')->with('success', 'Arsip jadwal berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        // ... (method ini tidak berubah)
        $query = Jadwal::query();
        $jenisSemester = $request->input('jenis_semester');
        $prodiId = $request->input('prodi_id');

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

        $prodi = $prodiId ? Prodi::find($prodiId) : null;
        $jadwalPerKelas = $query->with(['penugasan.mataKuliah', 'penugasan.dosen', 'penugasan.kelas.prodi', 'ruangan'])->get()->sortBy('penugasan.kelas.nama_kelas')->groupBy('penugasan.kelas.nama_kelas');
        $tanggalSekarang = Carbon::now()->translatedFormat('d F Y');

        return view('jadwal_cetak', compact('jadwalPerKelas', 'prodi', 'tanggalSekarang'));
    }
}
