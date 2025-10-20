<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penugasan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dosen_id',
        'mata_kuliah_id',
        'kelas_id',
    ];

    /**
     * Setiap penugasan terhubung ke satu Mata Kuliah.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Setiap penugasan terhubung ke satu Dosen.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Setiap penugasan terhubung ke satu Kelas.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Satu penugasan bisa memiliki banyak jadwal (jika SKS > 1).
     */
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'penugasan_id');
    }
}
