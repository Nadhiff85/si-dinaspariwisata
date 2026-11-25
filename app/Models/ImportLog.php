<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_file',
        'jenis_data',
        'total_baris',
        'baris_berhasil',
        'baris_gagal',
        'detail_gagal_json',
        'status',
        'diimpor_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_baris' => 'integer',
            'baris_berhasil' => 'integer',
            'baris_gagal' => 'integer',
            'detail_gagal_json' => 'array',
        ];
    }
}

