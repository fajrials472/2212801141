<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosenList = [
            'Drs. Usman Sangka, S.Sos.I., M.Pd.I',
            'Dr. Sumadin, S.Pd.I., M.Pd.I',
            'Dr. H. Hakzah, ST.,MT',
            'Dr. Ikhwan Sawaty, S.Pd.,M.Pd',
            'Andi Wafiah, S.Kom, M.Kom',
            'Wahyu Artanugraha, S.Kom.,M.Kom',
            'Muhammad Basri, S.T., MT.',
            'Masnur, ST.,M.Kom',
            'Marlina, S.Kom, M.Kom.',
            'Muhammad Nur Maallah, S.Ag., MA',
            'Hamra, S.Kom.,M.Kom',
            'Sudirman Sahidin, ST., M.Kom.',
            'Ade Hastuty, ST.,S.Kom, M.T.',
            'Dr. Nurdiansyah Sirimorok, S.Kom.,MM',
            'Mughaffir Yunus, S.T., M.T.',
            'Ir. Untung Suwardoyo, S.Kom., M.T.,IPP',
            'Wahyuddin, S.Kom.,M.Kom',
            'Dr. Raya Mangsi, S.Pd., M.Pd.I',
            'Prof. Dr. H. Mahsyar Idris, M.Ag',
            'Ahmad Selao, S.T.P, M.Sc',
            'Hasnawati, S.Kom.,M.Kom',
            'Guswayani Gunawan, S.Kom.,M.Kom',
            'Dr. Iradatullah Rahim, M.P.',
            'Dr. Hj. Henny Setiawati, S.Pd.,M.Pd',
        ];

        // Menggunakan transaction untuk memastikan semua data berhasil dibuat atau tidak sama sekali
        DB::transaction(function () use ($dosenList) {
            foreach ($dosenList as $namaDosen) {
                // Membuat data dummy yang diperlukan
                $email = Str::slug(Str::before($namaDosen, ',')) . '@umpar.ac.id';
                $nidn = '09' . rand(10000000, 99999999); // NIDN dummy
                $nbm = rand(100000, 999999); // NBM dummy

                // 1. Buat User terlebih dahulu
                $user = User::create([
                    'name'     => $namaDosen,
                    'email'    => $email,
                    'nidn'     => $nidn,
                    'nbm'      => $nbm,
                    'password' => Hash::make('password'), // Password default untuk semua dosen
                    'role'     => 'dosen',
                ]);

                // 2. Buat Dosen dan hubungkan dengan user_id yang baru dibuat
                Dosen::create([
                    'user_id'    => $user->id, // Ini adalah kunci penghubungnya
                    'nama_dosen' => $namaDosen,
                    'alamat'     => 'Parepare, Sulawesi Selatan', // Alamat default
                    'nbm'        => $nbm,
                    'nidn'       => $nidn,
                    'email'      => $email,
                ]);
            }
        });
    }
}
