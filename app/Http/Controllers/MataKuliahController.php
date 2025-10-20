<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $query = MataKuliah::query();
        $prodiId = $request->input('prodi_id');
        $jenisSemester = $request->input('jenis_semester');
        $search = $request->input('search');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                    ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        // Filter berdasarkan jenis semester (Gasal/Genap)
        if ($jenisSemester) {
            if ($jenisSemester === 'gasal') {
                $query->whereRaw('semester % 2 = 1');
            } elseif ($jenisSemester === 'genap') {
                $query->whereRaw('semester % 2 = 0');
            }
        }

        $mataKuliah = $query->with('prodi')->paginate(10);
        $prodi = Prodi::orderBy('nama_prodi')->get();

        return view('mata_kuliah', compact('mataKuliah', 'prodi', 'prodiId', 'jenisSemester', 'search'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'semester' => 'required|integer|min:1|max:14',
            'mata_kuliahs' => 'required|array|min:1',
            'mata_kuliahs.*.nama_mk' => 'required|string|max:255',
            'mata_kuliahs.*.kode_mk' => 'required|string|max:10|distinct|unique:mata_kuliah,kode_mk',
            'mata_kuliahs.*.sks' => 'required|integer|min:1|max:4',
        ]);

        foreach ($validatedData['mata_kuliahs'] as $mk) {
            MataKuliah::create([
                'nama_mk' => $mk['nama_mk'],
                'kode_mk' => $mk['kode_mk'],
                'sks' => $mk['sks'],
                'prodi_id' => $validatedData['prodi_id'],
                'semester' => $validatedData['semester'],
            ]);
        }

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'nama_mk' => 'required|string|max:255',
            'kode_mk' => ['required', 'string', 'max:10', Rule::unique('mata_kuliah')->ignore($mataKuliah->id)],
            'sks' => 'required|integer|min:1|max:4',
            'semester' => 'required|integer|min:1|max:8',
            'prodi_id' => 'required|exists:prodi,id',
        ]);

        $mataKuliah->update($validated);

        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();
        return redirect()->route('mata-kuliah.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
