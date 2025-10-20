<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Mahasiswa;
use SplFileObject;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file CSV Anda
        $csvFile = database_path('Daftar Mahasiswa.csv');

        // Pastikan file ada
        if (!file_exists($csvFile)) {
            $this->command->error('File CSV Daftar Mahasiswa.csv tidak ditemukan!');
            return;
        }

        $file = new SplFileObject($csvFile, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        $file->fgetcsv(); // Baca dan abaikan header

        foreach ($file as $row) {
            if (empty($row) || !isset($row[0]) || !isset($row[1])) {
                continue; // Skip baris yang tidak lengkap
            }

            // PERBAIKAN: Mengambil data dari kolom yang benar
            $nim = trim($row[0]);
            $nama = trim($row[1]);
            $alamat = trim($row[2] ?? 'Parepare');

            // Cek apakah mahasiswa sudah ada
            $mahasiswa = Mahasiswa::where('nim', $nim)->first();

            if (!$mahasiswa) {
                // Buat user untuk mahasiswa
                $user = User::create([
                    'name' => $nama,
                    'email' => $nim . '@mahasiswa.com', // Gunakan nim untuk email unik
                    'password' => Hash::make('password'), // Password default
                    'role' => 'mahasiswa',
                    'nim' => $nim,
                ]);

                // Buat data mahasiswa dan hubungkan dengan user
                Mahasiswa::create([
                    'nama' => $nama,
                    'nim' => $nim,
                    'alamat' => $alamat,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
