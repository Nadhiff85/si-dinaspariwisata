<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'peran',
        'destinasi_id',
        'bahasa',
        'tema',
        'kode_pengguna',
        'telepon',
        'asal_wilayah',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class, 'user_id');
    }

    public function tiket(): HasMany
    {
        return $this->hasMany(Tiket::class, 'user_id');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    public function isPengelola(): bool
    {
        return $this->peran === 'pengelola_destinasi';
    }
}
