<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    /**
     * Menampilkan daftar semua prodi dan form untuk menambah/mengedit.
     */
    public function index(Request $request)
    {
        $query = Prodi::query();

        // Logika untuk pencarian
        if ($request->has('search')) {
            $query->where('nama_prodi', 'like', '%' . $request->input('search') . '%');
        }

        // Logika untuk pagination (misal 10 data per halaman)
        $prodi = $query->paginate(10);

        return view('prodi', compact('prodi'));
    }

    /**
     * Menyimpan prodi baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_prodi' => 'required|string|max:255',
        ]);

        Prodi::create($request->all());

        return redirect()->route('prodi.index')->with('success', 'Prodi berhasil ditambahkan.');
    }

    /**
     * Memperbarui prodi yang sudah ada di database.
     */
    public function update(Request $request, Prodi $prodi)
    {
        $request->validate([
            'nama_prodi' => 'required|string|max:255',
        ]);

        $prodi->update($request->all());

        return redirect()->route('prodi.index')->with('success', 'Prodi berhasil diperbarui.');
    }

    /**
     * Menghapus prodi dari database.
     */
    public function destroy(Prodi $prodi)
    {
        $prodi->delete();
        return redirect()->route('prodi.index')->with('success', 'Prodi berhasil dihapus.');
    }
}
