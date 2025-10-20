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
        Schema::create('arsip_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_version_id')->constrained('jadwal_versions')->onDelete('cascade');
            $table->unsignedBigInteger('penugasan_id');
            $table->unsignedBigInteger('ruangan_id');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_jadwal');
    }
};
