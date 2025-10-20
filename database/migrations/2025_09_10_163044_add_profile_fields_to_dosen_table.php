<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void {
    Schema::table('mahasiswa', function (Blueprint $table) {
        $table->string('tempat_lahir')->nullable()->after('alamat');
        $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
        $table->string('no_hp')->nullable()->after('tanggal_lahir');
        $table->string('foto')->nullable()->after('no_hp');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            //
        });
    }
};
