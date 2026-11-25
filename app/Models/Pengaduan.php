<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode_pengaduan',
        'user_id',
        'destinasi_id',
        'nama_pelapor',
        'email_pelapor',
        'kategori',
        'judul',
        'pesan',
        'status',
        'tanggapan_admin',
        'ditanggapi_oleh',
        'ditanggapi_pada',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ditanggapi_pada' => 'datetime',
        ];
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }
}

