<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodiList = [
            'Teknik Informatika',
            'Teknik Sipil',
            'Teknik Elektro',
            'Teknik Perencanaan Wilayah Kota',
        ];

        foreach ($prodiList as $prodiName) {
            DB::table('prodi')->insert([
                'nama_prodi' => $prodiName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
