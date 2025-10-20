<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\DosenSeeder;
use Database\Seeders\MataKuliahSeeder;
use Database\Seeders\RuanganSeeder;
use Database\Seeders\KelasSeeder;
use Database\Seeders\MahasiswaSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProdiSeeder::class,
            DosenSeeder::class,
            MataKuliahSeeder::class,
            RuanganSeeder::class,
            KelasSeeder::class,
            MahasiswaSeeder::class,
        ]);
    }
}
