<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form edit profil.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Memperbarui profil pengguna (Dosen atau Mahasiswa).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Menentukan profil yang akan diupdate (dosen atau mahasiswa)
        $profile = $user->role === 'dosen' ? $user->dosen : $user->mahasiswa;

        // Validasi input dari form
        $request->validate([
            'alamat'        => 'nullable|string',
            'tempat_lahir'  => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_hp'         => 'nullable|string|max:20',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
            'password'      => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $updateData = $request->only(['alamat', 'tempat_lahir', 'tanggal_lahir', 'no_hp']);

        if ($request->hasFile('foto')) {
            if ($profile->foto) {
                Storage::disk('public')->delete($profile->foto);
            }
            $path = $request->file('foto')->store('profil', 'public');
            $updateData['foto'] = $path;
        }

        $profile->update($updateData);

        // Update password di tabel users jika diisi
        if ($request->filled('password')) {
            // PERBAIKAN: Mengambil ulang user dari database agar tipe data eksplisit
            $userToUpdate = User::find($user->id);
            if ($userToUpdate) {
                $userToUpdate->update([
                    'password' => $request->password,
                ]);
            }
        }

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}