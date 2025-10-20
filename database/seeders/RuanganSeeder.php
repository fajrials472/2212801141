<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kapasitas = 50; // Kapasitas default

        for ($i = 6; $i <= 16; $i++) {
            DB::table('ruangan')->insert([
                'nama_ruangan' => "F1.{$i}",
                'kapasitas' => $kapasitas,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
