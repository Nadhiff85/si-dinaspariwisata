<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunjunganHarian extends Model
{
    protected $table = 'kunjungan_harian';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tanggal',
        'destinasi_id',
        'wisnus',
        'wisman',
        'total_pengunjung',
        'kapasitas_harian',
        'keramaian_persen',
        'tingkat_keramaian',
        'diinput_oleh',
        'catatan_operasional',
        'sumber_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'wisnus' => 'integer',
            'wisman' => 'integer',
            'total_pengunjung' => 'integer',
            'kapasitas_harian' => 'integer',
            'keramaian_persen' => 'float',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }
}

