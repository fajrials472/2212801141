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

    /**
     * PERBAIKAN: Pastikan semua kolom (termasuk 'nama') ada di sini
     * dan 'user_id' tidak duplikat.
     */
    protected $fillable = [
        'nama', // Pastikan ini 'nama', BUKAN 'nama_mahasiswa'
        'nim',
        'alamat',
        'angkatan',
        'user_id',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'foto',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'mahasiswa_kelas');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($mahasiswa) {
            // Hapus user terkait jika ada
            if ($mahasiswa->user) {
                $mahasiswa->user->delete();
            }
        });
    }
}
