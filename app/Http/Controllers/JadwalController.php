<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Penugasan;
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

    public function requestGabung(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'mata_kuliah_id' => 'required',
            'kelas_ids'      => 'required|array',
            'jadwal_utama_id' => 'required', // Ini adalah ID KELAS yang jadi acuan
        ]);

        $mkId = $request->mata_kuliah_id;
        $kelasAcuanId = $request->jadwal_utama_id; // ID Kelas yang dipilih user sebagai "Master"
        $kelasTargetIds = $request->kelas_ids; // Array ID kelas yang mau diubah

        try {
            DB::beginTransaction();

            // 2. AMBIL DATA JADWAL DARI KELAS ACUAN (MASTER)
            // Kita cari jadwal milik kelas yang dipilih sebagai acuan
            $jadwalMaster = Jadwal::whereHas('penugasan', function ($q) use ($kelasAcuanId, $mkId) {
                $q->where('kelas_id', $kelasAcuanId)
                    ->where('mata_kuliah_id', $mkId);
            })->first();

            if (!$jadwalMaster) {
                return back()->with('error', 'Jadwal acuan tidak ditemukan!');
            }

            // 3. LOOPING UNTUK UPDATE KELAS-KELAS LAIN
            foreach ($kelasTargetIds as $targetKelasId) {

                // Jangan update diri sendiri (opsional, tapi lebih efisien)
                if ($targetKelasId == $kelasAcuanId) continue;

                // Cari jadwal milik kelas target
                $jadwalTarget = Jadwal::whereHas('penugasan', function ($q) use ($targetKelasId, $mkId) {
                    $q->where('kelas_id', $targetKelasId)
                        ->where('mata_kuliah_id', $mkId);
                })->first();

                if ($jadwalTarget) {
                    // 4. PROSES COPY-PASTE (UPDATE)
                    // Ubah jadwal target agar SAMA PERSIS dengan jadwal master
                    $jadwalTarget->update([
                        'hari'        => $jadwalMaster->hari,
                        'jam_mulai'   => $jadwalMaster->jam_mulai,
                        'jam_selesai' => $jadwalMaster->jam_selesai,
                        'ruangan_id'  => $jadwalMaster->ruangan_id, // Ikut ruangan master juga
                        // 'is_gabung' => true // (Opsional) Jika ada kolom penanda gabung
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Berhasil! Kelas telah digabungkan mengikuti jadwal pilihan Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
        // ... (Logika filter)
        $query = Jadwal::query();
        $jenisSemester = $request->input('jenis_semester'); // Variabel ini HARUS ada
        $prodiId = $request->input('prodi_id');

        // ... (Logika query)

        $prodi = $prodiId ? Prodi::find($prodiId) : null;
        $jadwalPerKelas = $query->with(['penugasan.mataKuliah', 'penugasan.dosen', 'penugasan.kelas.prodi', 'ruangan'])->get()->sortBy('penugasan.kelas.nama_kelas')->groupBy('penugasan.kelas.nama_kelas');
        $tanggalSekarang = Carbon::now()->translatedFormat('d F Y');

        // ====================================================================
        // Pastikan Blok Tahun Ajaran INI ADA
        // ====================================================================
        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = Carbon::now()->month;

        if ($bulanSekarang >= 7) {
            $tahunAwal = $tahunSekarang;
            $tahunAkhir = $tahunSekarang + 1;
        } else {
            $tahunAwal = $tahunSekarang - 1;
            $tahunAkhir = $tahunSekarang;
        }

        $tahunAjaranAktif = $tahunAwal . '/' . $tahunAkhir;
        // ====================================================================

        // ====================================================================
        // Pastikan Variabel DIKIRIMKAN dengan BENAR
        // ====================================================================
        return view('jadwal_cetak', compact('jadwalPerKelas', 'prodi', 'tanggalSekarang', 'jenisSemester', 'tahunAjaranAktif'));
        // ====================================================================
    }

    public function setujuiGabung($id)
    {
        // 1. Ambil data permintaan dari tabel request
        $requestGabung = \App\Models\RequestGabung::findOrFail($id);

        if ($requestGabung->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        // 2. Ambil ID Kelas yang mau diubah (Target) & ID Kelas Acuan
        // Logikanya: Dosen memilih jadwal milik salah satu kelas (Master) untuk dipakai kelas lain
        // PENTING: Kamu harus memastikan di form request Dosen tadi ada input 'jadwal_utama_id'
        // Jika belum ada, kita asumsikan kelas PERTAMA di list adalah Acuan/Masternya.

        $kelasIds = $requestGabung->kelas_ids; // Array [1, 2, 3]
        $kelasMasterId = $kelasIds[0]; // Kita ambil kelas pertama sebagai contoh jadwal yg benar

        // 3. Cari Jadwal Master (Jadwal yang mau dicopy)
        $jadwalMaster = Jadwal::whereHas('penugasan', function ($q) use ($requestGabung, $kelasMasterId) {
            $q->where('dosen_id', $requestGabung->dosen_id)
                ->where('kelas_id', $kelasMasterId)
                ->where('mata_kuliah_id', $requestGabung->mata_kuliah_id);
        })->first();

        if (!$jadwalMaster) {
            return back()->with('error', 'Jadwal sumber (Master) belum dibuat. Tidak bisa menggabungkan.');
        }

        // 4. EKSEKUSI: Loop ke kelas-kelas lain untuk samakan jadwalnya
        foreach ($kelasIds as $targetId) {
            if ($targetId == $kelasMasterId) continue; // Skip kelas master (jangan timpa diri sendiri)

            // Cari jadwal milik kelas target
            $jadwalTarget = Jadwal::whereHas('penugasan', function ($q) use ($requestGabung, $targetId) {
                $q->where('dosen_id', $requestGabung->dosen_id)
                    ->where('kelas_id', $targetId)
                    ->where('mata_kuliah_id', $requestGabung->mata_kuliah_id);
            })->first();

            // Update Jadwal Target
            if ($jadwalTarget) {
                $jadwalTarget->update([
                    'hari'        => $jadwalMaster->hari,
                    'jam_mulai'   => $jadwalMaster->jam_mulai,
                    'jam_selesai' => $jadwalMaster->jam_selesai,
                    'ruangan_id'  => $jadwalMaster->ruangan_id,
                ]);
            }
        }

        // 5. Ubah Status Permintaan jadi "Disetujui"
        $requestGabung->update(['status' => 'disetujui']);

        return back()->with('success', 'Jadwal berhasil digabungkan!');
    }
}
