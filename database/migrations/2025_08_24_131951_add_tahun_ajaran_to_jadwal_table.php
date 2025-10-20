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
            $table->string('tahun_ajaran')->nullable()->after('ruangan_id');
            $table->string('jenis_semester')->nullable()->after('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropColumn('tahun_ajaran');
            $table->dropColumn('jenis_semester');
        });
    }
};
