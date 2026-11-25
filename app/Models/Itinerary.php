<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Itinerary extends Model
{
    protected $table = 'itineraries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'judul_perjalanan',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'jumlah_dewasa',
        'jumlah_anak',
        'estimasi_biaya_tiket_rp',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date:Y-m-d',
            'tanggal_selesai' => 'date:Y-m-d',
            'jumlah_hari' => 'integer',
            'jumlah_dewasa' => 'integer',
            'jumlah_anak' => 'integer',
            'estimasi_biaya_tiket_rp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItineraryItem::class, 'itinerary_id')->orderBy('hari_ke')->orderBy('urutan');
    }
}

