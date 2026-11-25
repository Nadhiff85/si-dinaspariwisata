<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tiket extends Model
{
    protected $table = 'tiket';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'kode_tiket',
        'destinasi_id',
        'user_id',
        'kode_pengguna',
        'nama_pemesan',
        'email_pemesan',
        'tanggal_pesan',
        'tanggal_kunjungan',
        'jumlah_dewasa',
        'jumlah_anak',
        'harga_dewasa',
        'harga_anak',
        'total_bayar_rp',
        'metode_bayar',
        'status',
        'asal_pemesan',
        'waktu_checkin',
        'diverifikasi_oleh',
        'sumber_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pesan' => 'date:Y-m-d',
            'tanggal_kunjungan' => 'date:Y-m-d',
            'jumlah_dewasa' => 'integer',
            'jumlah_anak' => 'integer',
            'harga_dewasa' => 'integer',
            'harga_anak' => 'integer',
            'total_bayar_rp' => 'integer',
            'waktu_checkin' => 'datetime',
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

