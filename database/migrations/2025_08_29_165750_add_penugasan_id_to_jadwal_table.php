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
        Schema::table('jadwal', function (Blueprint $table) {
            // Menambahkan kolom foreign key 'penugasan_id'
            // 'after('id')' bersifat opsional, hanya untuk merapikan urutan kolom
            $table->foreignId('penugasan_id')
                ->nullable() // <-- PERUBAHAN KUNCI: Izinkan kolom ini untuk kosong (NULL)
                ->constrained('penugasan')
                ->onDelete('cascade') // Jika penugasan dihapus, jadwal ikut terhapus
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Urutan drop harus Foreign dulu baru Column
            $table->dropForeign(['penugasan_id']);
            $table->dropColumn('penugasan_id');
        });
    }
};
