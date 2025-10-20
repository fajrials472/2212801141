<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nama_mahasiswa',
        'nim',
        'alamat',
        'user_id',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'foto',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Kelas that the Mahasiswa is enrolled in.
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'mahasiswa_kelas');
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($mahasiswa) {
            $mahasiswa->user()->delete();
        });
    }
}
