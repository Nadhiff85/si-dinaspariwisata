<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    protected $table = 'itinerary_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'itinerary_id',
        'destinasi_id',
        'hari_ke',
        'urutan',
        'jam_mulai',
        'durasi_menit',
        'estimasi_biaya_rp',
        'catatan_item',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hari_ke' => 'integer',
            'urutan' => 'integer',
            'durasi_menit' => 'integer',
            'estimasi_biaya_rp' => 'integer',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }
}

