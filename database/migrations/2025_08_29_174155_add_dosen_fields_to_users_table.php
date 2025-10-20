<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // PERBAIKAN: Menambahkan pengecekan sebelum membuat kolom
            
            // Periksa apakah kolom 'nidn' belum ada
            if (!Schema::hasColumn('users', 'nidn')) {
                $table->string('nidn')->nullable()->unique()->after('email');
            }

            // Periksa apakah kolom 'nbm' belum ada
            if (!Schema::hasColumn('users', 'nbm')) {
                $table->string('nbm')->nullable()->unique()->after('nidn');
            }

            // Periksa apakah kolom 'role' belum ada
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('mahasiswa')->after('nbm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pastikan kolom ada sebelum mencoba menghapusnya
            if (Schema::hasColumn('users', 'nidn')) {
                $table->dropColumn('nidn');
            }
            if (Schema::hasColumn('users', 'nbm')) {
                $table->dropColumn('nbm');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};


