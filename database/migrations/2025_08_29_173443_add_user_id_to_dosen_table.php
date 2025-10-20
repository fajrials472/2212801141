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
        // Periksa apakah kolomnya belum ada sebelum menambahkan
        if (!Schema::hasColumn('dosen', 'user_id')) {
            Schema::table('dosen', function (Blueprint $table) {
                // Menambahkan kolom user_id yang terhubung ke tabel users
                // onDelete('cascade') berarti jika User dihapus, data Dosen terkait juga akan ikut terhapus.
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};




