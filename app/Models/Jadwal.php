<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi langsung ke model MataKuliah.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Mendefinisikan relasi langsung ke model Dosen.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Mendefinisikan relasi langsung ke model Kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Mendefinisikan relasi langsung ke model Ruangan.
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    /**
     * (Opsional) Relasi ke Penugasan jika kolom penugasan_id akan digunakan di masa depan.
     */
    public function penugasan()
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }
}
