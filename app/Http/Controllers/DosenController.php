<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class DosenController extends Controller
{
    /**
     * Menampilkan daftar dosen.
     */
    public function index(Request $request)
    {
        $query = Dosen::query();
        $prodiId = $request->input('prodi_id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_dosen', 'like', "%{$search}%")
                    ->orWhere('nidn', 'like', "%{$search}%")
                    ->orWhere('nbm', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($prodiId) {
            $query->whereHas('prodi', function ($q) use ($prodiId) {
                $q->where('prodi.id', $prodiId);
            });
        }

        $dosen = $query->with('prodi')->latest()->paginate(10);
        $prodi = Prodi::orderBy('nama_prodi')->get();

        return view('dosen', compact('dosen', 'prodi', 'prodiId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_dosen' => 'required|string|max:255',
            'nidn'       => 'required|string|unique:users,nidn|unique:dosen,nidn',
            'email'      => 'required|email|unique:users,email',
            'alamat'     => 'required|string',
            'prodi'      => 'required|array',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // 1. Buat User terlebih dahulu dengan password default
            $user = User::create([
                'name'     => $validated['nama_dosen'],
                'email'    => $validated['email'],
                'nidn'     => $validated['nidn'],
                'password' => Hash::make('password123'), // Ganti dengan password default yang aman
                'role'     => 'dosen',
            ]);

            // 2. Buat profil Dosen dan hubungkan dengan user_id
            $dosen = Dosen::create([
                'user_id'    => $user->id,
                'nama_dosen' => $validated['nama_dosen'],
                'nidn'       => $validated['nidn'],
                'email'      => $validated['email'],
                'alamat'     => $validated['alamat'],
                'nbm'        => $request->nbm,
            ]);

            // 3. Sambungkan Dosen dengan Prodi
            $dosen->prodi()->sync($validated['prodi']);
        });

        return redirect()->route('dosen.index')->with('success', 'Dosen baru berhasil ditambahkan.');
    }

    /**
     * Mengupdate data dosen DAN user terkait secara sinkron.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'nama_dosen' => 'required|string|max:255',
            'alamat'     => 'required|string',
            'nbm'        => ['nullable', 'string', 'max:20', Rule::unique('dosen')->ignore($dosen->id)],
            'nidn'       => ['required', 'string', 'max:20', Rule::unique('dosen')->ignore($dosen->id)],
            // Pastikan email unik di tabel users, tapi abaikan user yang sedang diedit
            'email'      => ['required', 'email', 'max:255', Rule::unique('users')->ignore($dosen->user_id)],
            'prodi'      => 'required|array',
            'prodi.*'    => 'exists:prodi,id',
        ]);

        DB::transaction(function () use ($dosen, $validated, $request) {
            // 1. Update data di tabel Dosen
            $dosen->update($validated);

            // 2. Update data User terkait melalui relasi (cara yang benar dan aman)
            if ($dosen->user) {
                $dosen->user->update([
                    'name'  => $validated['nama_dosen'],
                    'email' => $validated['email'],
                    'nidn'  => $validated['nidn'],
                    'nbm'   => $validated['nbm'] ?? null,
                ]);
            }

            // 3. Sinkronkan prodi
            $dosen->prodi()->sync($request->prodi);
        });

        return redirect()->route('dosen.index')->with('success', 'Data dosen dan user berhasil diupdate.');
    }

    /**
     * Menghapus data dosen DAN user terkait secara sinkron.
     */
    public function destroy(Dosen $dosen)
    {
        // Dengan onDelete('cascade') pada migrasi, kita hanya perlu menghapus User,
        // maka data Dosen yang terhubung akan otomatis terhapus.
        if ($dosen->user) {
            // Hapus relasi prodi terlebih dahulu
            $dosen->prodi()->detach();
            // Hapus user, yang akan otomatis menghapus data dosen karena 'cascade'
            $dosen->user->delete();
        } else {
            // Sebagai pengaman jika karena suatu hal user tidak terhubung
            $dosen->delete();
        }

        return redirect()->route('dosen.index')->with('success', 'Dosen beserta akun user-nya berhasil dihapus.');
    }
}
