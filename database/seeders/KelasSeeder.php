<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Prodi;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodiTI = Prodi::where('nama_prodi', 'Teknik Informatika')->first();

        if (!$prodiTI) {
            $this->command->info('Prodi Teknik Informatika tidak ditemukan. Seeder dihentikan.');
            return;
        }

        $kelasVarian = ['A', 'B', 'C', 'F'];
        $semesters = [2, 4, 6];
        $jumlahMahasiswa = 40;

        foreach ($semesters as $semester) {
            foreach ($kelasVarian as $kelas) {
                // Tentukan nama kelas berdasarkan semester
                $tahunMasuk = ($semester == 2) ? 24 : (($semester == 4) ? 23 : 22); // Contoh logika tahun masuk
                $namaKelas = "TI-{$kelas}-{$tahunMasuk}";

                DB::table('kelas')->insert([
                    'nama_kelas' => $namaKelas,
                    'jumlah_mahasiswa' => $jumlahMahasiswa,
                    'prodi_id' => $prodiTI->id,
                    'semester' => $semester,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
