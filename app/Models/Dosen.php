<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $fillable = [
        'nama_dosen',
        'nidn',
        'nbm',
        'email',
        'alamat',
        'user_id',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'foto',
        'user_id'
    ];

    /**
     * Get the User that owns the Dosen.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Prodi that belong to the Dosen.
     */
    public function prodi(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'dosen_prodi', 'dosen_id', 'prodi_id');
    }

    /**
     * The MataKuliahs that the Dosen teaches.
     */
    public function mataKuliah(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'penugasan', 'dosen_id', 'mata_kuliah_id');
    }
}
