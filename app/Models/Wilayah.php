<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'kode_bps',
        'nama_wilayah',
        'jenis',
        'latitude',
        'longitude',
        'deskripsi_id',
        'deskripsi_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function destinasi(): HasMany
    {
        return $this->hasMany(Destinasi::class, 'wilayah_id');
    }

    public function hotelBulanan(): HasMany
    {
        return $this->hasMany(HotelBulanan::class, 'wilayah_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'wilayah_id');
    }
}

