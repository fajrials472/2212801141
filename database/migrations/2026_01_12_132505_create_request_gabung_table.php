<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menampung permintaan penggabungan kelas dari Dosen.
     */
    public function up(): void
    {
        Schema::create('request_gabung', function (Blueprint $table) {
            $table->id();
            // Siapa dosen yang minta?
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('cascade');

            // Mata kuliah apa?
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade');

            // Kelas apa saja yang mau digabung? (Disimpan dalam format JSON, misal: [1, 2, 5])
            $table->json('kelas_ids');

            // Status permintaan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Alasan penolakan (opsional, jika admin menolak)
            $table->string('alasan_penolakan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_gabung');
    }
};
