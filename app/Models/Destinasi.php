<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destinasi extends Model
{
    protected $table = 'destinasi';

    public $incrementing = false;

    protected $appends = ['foto_attribution'];

    protected function fotoUtama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->foto_attribution
                ? sprintf('/images/destinasi/destinasi-%02d.webp', $this->id)
                : $value,
        );
    }

    protected function fotoAttribution(): Attribute
    {
        return Attribute::make(
            get: fn (): ?array => config('destination_photos.'.$this->id),
        );
    }

    protected function galeriJson(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): array => $this->foto_attribution
                ? [$this->foto_utama]
                : (json_decode($value ?? '[]', true) ?: []),
        );
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'slug',
        'nama_destinasi',
        'wilayah_id',
        'kategori',
        'latitude',
        'longitude',
        'harga_tiket_dewasa',
        'harga_tiket_anak',
        'jam_operasional',
        'kapasitas_harian',
        'deskripsi_id',
        'deskripsi_en',
        'fasilitas_json',
        'galeri_json',
        'foto_utama',
        'rating_rata_rata',
        'jumlah_ulasan',
        'tingkat_keramaian_hari_ini',
        'keramaian_persen_hari_ini',
        'is_unggulan',
        'status_aktif',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'harga_tiket_dewasa' => 'integer',
            'harga_tiket_anak' => 'integer',
            'kapasitas_harian' => 'integer',
            'fasilitas_json' => 'array',
            'galeri_json' => 'array',
            'rating_rata_rata' => 'float',
            'jumlah_ulasan' => 'integer',
            'keramaian_persen_hari_ini' => 'float',
            'is_unggulan' => 'boolean',
            'status_aktif' => 'boolean',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function kunjunganHarian(): HasMany
    {
        return $this->hasMany(KunjunganHarian::class, 'destinasi_id');
    }

    public function kunjunganBulanan(): HasMany
    {
        return $this->hasMany(KunjunganBulanan::class, 'destinasi_id');
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'destinasi_id');
    }

    public function tiket(): HasMany
    {
        return $this->hasMany(Tiket::class, 'destinasi_id');
    }

    public function pengelola(): HasMany
    {
        return $this->hasMany(User::class, 'destinasi_id');
    }
}
