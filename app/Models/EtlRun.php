<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtlRun extends Model
{
    protected $table = 'etl_runs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'jenis_proses',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_detik',
        'jumlah_baris_diproses',
        'metrik_json',
        'ringkasan_log',
        'dipicu_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'durasi_detik' => 'float',
            'jumlah_baris_diproses' => 'integer',
            'metrik_json' => 'array',
        ];
    }
}

