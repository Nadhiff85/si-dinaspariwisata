<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends Model
{
    protected $table = 'berita';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'judul_id',
        'judul_en',
        'ringkasan_id',
        'ringkasan_en',
        'isi_id',
        'isi_en',
        'kategori_berita',
        'wilayah_id',
        'penulis',
        'tanggal_terbit',
        'views_count',
        'is_featured',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date:Y-m-d',
            'views_count' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
}

