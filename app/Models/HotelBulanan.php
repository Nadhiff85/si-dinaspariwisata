<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelBulanan extends Model
{
    protected $table = 'hotel_bulanan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'wilayah_id',
        'tahun',
        'bulan',
        'periode',
        'jumlah_hotel_bintang',
        'jumlah_kamar_bintang',
        'tpk_persen',
        'tamu_domestik',
        'tamu_asing',
        'rata_lama_menginap_hari',
        'sumber_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'jumlah_hotel_bintang' => 'integer',
            'jumlah_kamar_bintang' => 'integer',
            'tpk_persen' => 'float',
            'tamu_domestik' => 'integer',
            'tamu_asing' => 'integer',
            'rata_lama_menginap_hari' => 'float',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
}

