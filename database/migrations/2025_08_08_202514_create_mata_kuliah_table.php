<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mk');
            $table->string('kode_mk')->unique(); // This column should be here
            $table->integer('sks');
            $table->integer('semester');
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
