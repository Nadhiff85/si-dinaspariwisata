<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ulasan extends Model
{
    protected $table = 'ulasan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'destinasi_id',
        'user_id',
        'kode_pengguna',
        'tanggal',
        'nama_pengguna',
        'bahasa',
        'rating',
        'skor_pemandangan',
        'skor_kebersihan',
        'skor_akses',
        'skor_fasilitas',
        'skor_harga',
        'teks_ulasan',
        'label_sentimen',
        'skor_sentimen',
        'balasan_pengelola',
        'dibalas_pada',
        'dibalas_oleh',
        'status_moderasi',
        'alasan_lapor',
        'sumber_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
            'rating' => 'integer',
            'skor_pemandangan' => 'integer',
            'skor_kebersihan' => 'integer',
            'skor_akses' => 'integer',
            'skor_fasilitas' => 'integer',
            'skor_harga' => 'integer',
            'skor_sentimen' => 'float',
            'dibalas_pada' => 'datetime',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

