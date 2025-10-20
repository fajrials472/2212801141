<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user Admin
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        // Buat user untuk setiap Dosen
        $dosen = Dosen::all();
        foreach ($dosen as $d) {
            $user = User::firstOrCreate(
                ['email' => $d->email],
                ['name' => $d->nama_dosen, 'password' => Hash::make('password'), 'role' => 'dosen']
            );
            $d->user_id = $user->id;
            $d->save();
        }

        // Buat user untuk setiap Kelas (proxy untuk mahasiswa)
        $kelas = Kelas::all();
        foreach ($kelas as $k) {
            $user = User::firstOrCreate(
                ['email' => strtolower($k->nama_kelas) . '@mahasiswa.com'],
                ['name' => 'Mahasiswa ' . $k->nama_kelas, 'password' => Hash::make('password'), 'role' => 'mahasiswa']
            );
            $k->user_id = $user->id;
            $k->save();
        }
    }
}
