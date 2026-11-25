<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $table = 'events';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'nama_event_id',
        'nama_event_en',
        'wilayah_id',
        'destinasi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi_detail',
        'kategori_event',
        'deskripsi_id',
        'deskripsi_en',
        'harga_tiket_rp',
        'status_persetujuan',
        'dibuat_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date:Y-m-d',
            'tanggal_selesai' => 'date:Y-m-d',
            'harga_tiket_rp' => 'integer',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }
}

