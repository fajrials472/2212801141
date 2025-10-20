<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodi';

    protected $fillable = ['nama_prodi', 'kode_prodi'];

    /**
     * The Dosen that belong to the Prodi.
     */
    public function dosen(): BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'dosen_prodi', 'prodi_id', 'dosen_id');
    }

    /**
     * Get the MataKuliah for the Prodi.
     */
    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class);
    }

    /**
     * Get the Kelas for the Prodi.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
