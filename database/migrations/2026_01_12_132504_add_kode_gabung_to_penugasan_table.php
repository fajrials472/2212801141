<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom kode_gabung untuk menandai kelas yang harus dijadwalkan bersamaan.
     */
    public function up(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            // Kolom ini akan menyimpan kode unik (misal: "GAB-XYZ123")
            // Jika NULL, berarti kelas ini berdiri sendiri (normal).
            $table->string('kode_gabung')->nullable()->after('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropColumn('kode_gabung');
        });
    }
};
