<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestGabung extends Model
{
    use HasFactory;

    protected $table = 'request_gabung';

    protected $fillable = [
        'dosen_id',
        'mata_kuliah_id',
        'kelas_ids',
        'status',
        'alasan_penolakan'
    ];

    protected $casts = [
        'kelas_ids' => 'array', // Otomatis ubah JSON ke Array saat diambil
    ];

    // Relasi ke Dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    // Relasi ke Mata Kuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
