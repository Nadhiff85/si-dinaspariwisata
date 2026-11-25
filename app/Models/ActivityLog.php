<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nama_user',
        'peran',
        'aksi',
        'modul',
        'deskripsi',
        'metadata_json',
        'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
        ];
    }
}

