<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::query();
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_kelas', 'like', "%{$request->search}%");
        }

        // Perbaikan: Mengurutkan kelas berdasarkan angkatan dan nama_kelas
        $kelas = $query->with(['prodi', 'mahasiswa'])
            ->orderBy('angkatan', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->paginate(10);

        $prodi = Prodi::orderBy('nama_prodi')->get();

        // Mengambil semua mahasiswa dengan relasi kelas
        $mahasiswa = Mahasiswa::with('kelas')->orderBy('nim')->get();

        return view('kelas', compact('kelas', 'prodi', 'mahasiswa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
            'angkatan' => 'required|string|max:255',
            'prodi_id' => 'required|exists:prodi,id',
            'semester' => 'required|integer|min:1|max:14',
            // 1. Ubah validasi 'mahasiswa' menjadi opsional (nullable)
            'mahasiswa' => 'nullable|array',
            'mahasiswa.*' => 'exists:mahasiswa,id',
        ]);

        // 2. Hitung jumlah mahasiswa dengan aman (beri nilai default array kosong)
        $jumlahMahasiswa = count($request->input('mahasiswa', []));

        $kelas = Kelas::create([
            'nama_kelas' => $validated['nama_kelas'],
            'angkatan' => $validated['angkatan'],
            'prodi_id' => $validated['prodi_id'],
            'semester' => $validated['semester'],
            'jumlah_mahasiswa' => $jumlahMahasiswa
        ]);

        // 3. Hanya 'attach' mahasiswa jika ada yang dipilih dari form
        if ($request->has('mahasiswa')) {
            $kelas->mahasiswa()->attach($validated['mahasiswa']);
        }

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Kelas $kelas) // Mengharapkan $kelas
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255', Rule::unique('kelas')->ignore($kelas->id)],
            'angkatan' => 'required|string|max:255',
            'prodi_id' => 'required|exists:prodi,id',
            'semester' => 'required|integer|min:1|max:14',
            // 1. Ubah validasi 'mahasiswa' menjadi opsional (nullable)
            'mahasiswa' => 'nullable|array',
            'mahasiswa.*' => 'exists:mahasiswa,id',
        ]);

        $kelas->update($validated);

        // 2. Gunakan 'sync' dengan aman (beri nilai default array kosong)
        $mahasiswaIds = $request->input('mahasiswa', []);
        $kelas->mahasiswa()->sync($mahasiswaIds);

        // 3. Update jumlah mahasiswa dengan aman
        $kelas->jumlah_mahasiswa = count($mahasiswaIds);
        $kelas->save();

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->mahasiswa()->detach();
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
