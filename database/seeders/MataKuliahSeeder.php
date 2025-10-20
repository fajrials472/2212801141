<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Prodi; // Pastikan Model Prodi diimpor

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataKuliahList = [
            // Semester II
            ['nama_mk' => 'Al-Islam / Kemuhammadiyahan II', 'sks' => 1, 'semester' => 2],
            ['nama_mk' => 'Kewirausahaan', 'sks' => 2, 'semester' => 2],
            ['nama_mk' => 'Kearifan Lokal / Khasanah Bugis', 'sks' => 2, 'semester' => 2],
            ['nama_mk' => 'Struktur Data', 'sks' => 3, 'semester' => 2],
            ['nama_mk' => 'Struktur Diskrit', 'sks' => 3, 'semester' => 2],
            ['nama_mk' => 'Aljabar Linier', 'sks' => 3, 'semester' => 2],
            ['nama_mk' => 'Sistem Basis Data', 'sks' => 3, 'semester' => 2],
            ['nama_mk' => 'Metode Statistika', 'sks' => 3, 'semester' => 2],

            // Semester IV
            ['nama_mk' => 'Al-Islam / Kemuhammadiyahan IV', 'sks' => 1, 'semester' => 4],
            ['nama_mk' => 'Grafika Komputer', 'sks' => 3, 'semester' => 4],
            ['nama_mk' => 'Kecerdasan Buatan', 'sks' => 3, 'semester' => 4],
            ['nama_mk' => 'Desain dan Analisis Algoritma', 'sks' => 3, 'semester' => 4],
            ['nama_mk' => 'Pemrograman Web', 'sks' => 3, 'semester' => 4],
            ['nama_mk' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'semester' => 4],
            ['nama_mk' => 'Keamanan Komputer', 'sks' => 3, 'semester' => 4],

            // Semester VI
            ['nama_mk' => 'Al-Islam / Kemuhammadiyahan VI', 'sks' => 1, 'semester' => 6],
            ['nama_mk' => 'Pemodelan dan Simulasi', 'sks' => 2, 'semester' => 6],
            ['nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Mobile Computing', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Tata Tulis Ilmiah dan Presentasi', 'sks' => 2, 'semester' => 6],
            ['nama_mk' => 'Sistem Penunjang Keputusan', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Sistem Informasi Geografis', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Pemrosesan Bahasa Alami', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Augmented dan Virtual Reality', 'sks' => 3, 'semester' => 6],
            ['nama_mk' => 'Web Semantik', 'sks' => 3, 'semester' => 6],
        ];

        // Dapatkan ID Prodi Teknik Informatika. Jika tidak ada, buat baru.
        $prodiTI = Prodi::firstOrCreate(
            ['nama_prodi' => 'Teknik Informatika'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $prodiId = $prodiTI->id;

        foreach ($mataKuliahList as $mk) {
            DB::table('mata_kuliah')->insert([
                'nama_mk' => $mk['nama_mk'],
                'kode_mk' => Str::upper(Str::random(5)), // Membuat kode MK acak 5 karakter
                'sks' => $mk['sks'],
                'semester' => $mk['semester'],
                'prodi_id' => $prodiId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
