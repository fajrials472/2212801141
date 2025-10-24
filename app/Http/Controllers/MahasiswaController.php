<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    /**
     * Menampilkan daftar mahasiswa.
     */
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['user.kelas', 'kelas']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $mahasiswa = $query->latest()->paginate(10);
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('mahasiswa', compact('mahasiswa', 'kelas'));
    }

    /**
     * PERBAIKAN: Menyimpan mahasiswa baru DAN membuat user terkait.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|string|max:20|unique:mahasiswa,nim|unique:users,nim',
            'alamat' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat User terlebih dahulu
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['nim'] . '@no-email.com', // Email dummy unik
                'nim' => $validated['nim'],
                'password' => Hash::make('password123'), // Password default
                'role' => 'mahasiswa',
                'kelas_id' => null,
            ]);

            // 2. Buat profil Mahasiswa dan hubungkan dengan user_id
            Mahasiswa::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'nim' => $validated['nim'],
                'alamat' => $validated['alamat'],
            ]);
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan beserta akun user-nya.');
    }


    /**
     * FITUR BARU: Membuat akun User untuk mahasiswa yang sudah ada
     */
    public function createUser(Mahasiswa $mahasiswa)
    {
        // 1. Cek dulu apakah user_id sudah terisi
        if ($mahasiswa->user_id) {
            return redirect()->back()->with('error', 'Mahasiswa ini sudah memiliki akun user.');
        }

        // 2. Cek apakah NIM/Email sudah terpakai di tabel users
        $emailDummy = $mahasiswa->nim . '@no-email.com';
        $userExists = User::where('nim', $mahasiswa->nim)->orWhere('email', $emailDummy)->exists();

        if ($userExists) {
            return redirect()->back()->with('error', 'User dengan NIM/Email ini sudah ada. Sinkronisasi manual diperlukan.');
        }

        // 3. Buat user baru
        $user = User::create([
            'name' => $mahasiswa->nama,
            'email' => $emailDummy,
            'nim' => $mahasiswa->nim,
            'password' => Hash::make('password123'), // Password default
            'role' => 'mahasiswa',
        ]);

        // 4. Sambungkan user baru ke mahasiswa
        $mahasiswa->user_id = $user->id;
        $mahasiswa->save();

        return redirect()->route('mahasiswa.index')->with('success', 'Akun user untuk ' . $mahasiswa->nama . ' berhasil dibuat.');
    }
    /**
     * Mengupdate data mahasiswa DAN user terkait.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $userIdToIgnore = $mahasiswa->user_id;

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => ['required', 'string', 'max:20', Rule::unique('mahasiswa')->ignore($mahasiswa->id), Rule::unique('users')->ignore($userIdToIgnore)],
            'alamat' => 'nullable|string',
        ]);

        DB::transaction(function () use ($mahasiswa, $validated) {
            $mahasiswa->update($validated);

            if ($mahasiswa->user) {
                $mahasiswa->user->update([
                    'name' => $validated['nama'],
                    'nim' => $validated['nim'],
                ]);
            }
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Menghapus data mahasiswa DAN user terkait.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        // Gunakan transaction untuk keamanan
        DB::transaction(function () use ($mahasiswa) {
            // Hapus user terlebih dahulu (jika terhubung)
            // Ini akan otomatis menghapus mahasiswa jika foreign key diatur onDelete('cascade')
            if ($mahasiswa->user) {
                $mahasiswa->user->delete();
            } else {
                // Fallback jika user tidak terhubung, hapus mahasiswanya saja
                $mahasiswa->delete();
            }
        });

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }

    /**
     * Menambahkan mahasiswa ke dalam kelas melalui tabel pivot.
     */
    public function assignKelas(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'kelas_id'     => 'required|exists:kelas,id',
        ]);

        $mahasiswa = Mahasiswa::find($request->mahasiswa_id);

        // Sync akan mengatur kelas mahasiswa di tabel pivot
        $mahasiswa->kelas()->sync([$request->kelas_id]);

        return back()->with('success', 'Kelas mahasiswa berhasil diatur.');
    }
}
