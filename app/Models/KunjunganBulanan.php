<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganBulanan extends Model
{
    protected $table = 'kunjungan_bulanan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'destinasi_id',
        'tahun',
        'bulan',
        'periode',
        'wisnus',
        'wisman',
        'total_pengunjung',
        'pendapatan_tiket_rp',
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
            'wisnus' => 'integer',
            'wisman' => 'integer',
            'total_pengunjung' => 'integer',
            'pendapatan_tiket_rp' => 'integer',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }
}

