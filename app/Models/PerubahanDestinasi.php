<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerubahanDestinasi extends Model
{
    protected $table = 'perubahan_destinasi';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'destinasi_id',
        'pengelola_id',
        'jenis_perubahan',
        'data_lama_json',
        'data_baru_json',
        'alasan_perubahan',
        'status',
        'catatan_admin',
        'diproses_oleh',
        'diproses_pada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_lama_json' => 'array',
            'data_baru_json' => 'array',
            'diproses_pada' => 'datetime',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }

    public function pengelola(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengelola_id');
    }
}

