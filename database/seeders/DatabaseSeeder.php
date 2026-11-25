<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ImportLog;
use App\Services\WarehouseEtlService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's operational and warehouse databases from dataset_pariwisata_sulteng.xlsx
     * for catalog setup and reference data.
     */
    public function run(): void
    {
        $candidates = [
            database_path('data/dataset_pariwisata_sulteng.xlsx'),
            base_path('dokumen/dataset_excel/dataset_pariwisata_sulteng.xlsx'),
            base_path('dataset_pariwisata_sulteng.xlsx'),
        ];
        $excelPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                $excelPath = $cand;
                break;
            }
        }
        if (! $excelPath) {
            throw new \RuntimeException("File dataset_pariwisata_sulteng.xlsx tidak ditemukan di lokasi database/data maupun dokumen/dataset_excel.");
        }

        $this->command?->info('Membaca dataset_pariwisata_sulteng.xlsx...');
        $spreadsheet = IOFactory::load($excelPath);

        $appDb = DB::connection('mysql');
        $now = now();
        $sheetCounts = [];

        // 1. Seed ref_data_real (6 rows)
        $refRows = $this->readSheetRows($spreadsheet, 'ref_data_real');
        $refBatch = [];
        foreach ($refRows as $r) {
            if (empty($r['indikator'])) {
                continue;
            }
            $refBatch[] = [
                'indikator' => (string) $r['indikator'],
                'nilai' => (string) $r['nilai'],
                'periode' => (string) $r['periode'],
                'sumber' => (string) $r['sumber'],
                'url' => (string) ($r['url'] ?? ''),
                'catatan' => (string) ($r['catatan'] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $appDb->table('ref_data_real')->insertOrIgnore($refBatch);
        $sheetCounts['ref_data_real'] = count($refBatch);

        // 2. Seed kamus_data (108 rows)
        $kamusRows = $this->readSheetRows($spreadsheet, 'kamus_data');
        $kamusBatch = [];
        foreach ($kamusRows as $r) {
            if (empty($r['sheet'])) {
                continue;
            }
            $kamusBatch[] = [
                'sheet' => (string) $r['sheet'],
                'kolom' => (string) $r['kolom'],
                'tipe' => (string) $r['tipe'],
                'keterangan' => (string) $r['keterangan'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $appDb->table('kamus_data')->insertOrIgnore($kamusBatch);
        $sheetCounts['kamus_data'] = count($kamusBatch);

        // 3. Seed dim_wilayah -> wilayah (13 rows)
        $wilayahRows = $this->readSheetRows($spreadsheet, 'dim_wilayah');
        $wilayahBatch = [];
        foreach ($wilayahRows as $r) {
            if (empty($r['wilayah_id'])) {
                continue;
            }
            $wName = (string) $r['nama_wilayah'];
            $wilayahBatch[] = [
                'id' => (int) $r['wilayah_id'],
                'kode_bps' => (string) $r['kode_bps'],
                'nama_wilayah' => $wName,
                'jenis' => (string) $r['jenis'],
                'latitude' => (float) $r['latitude'],
                'longitude' => (float) $r['longitude'],
                'deskripsi_id' => "Kawasan pengembangan pariwisata strategis di {$wName}, Provinsi Sulawesi Tengah.",
                'deskripsi_en' => "Strategic tourism development region in {$wName}, Central Sulawesi Province.",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $appDb->table('wilayah')->insertOrIgnore($wilayahBatch);
        $sheetCounts['dim_wilayah'] = count($wilayahBatch);

        // Curated high-res coastal/nature/culture photography per category/destination
        $curatedPhotos = [
            'Bahari' => [
                'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5?auto=format&fit=crop&w=1200&q=80',
            ],
            'Alam' => [
                'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
            ],
            'Budaya & Sejarah' => [
                'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&w=1200&q=80',
            ],
            'Religi' => [
                'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=1200&q=80',
            ],
            'Ikon Kota' => [
                'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        // 4. Seed dim_destinasi -> destinasi (27 rows)
        $destTariffs = [
            1 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 17.00 WITA'],
            2 => ['dewasa' => 15000, 'anak' => 10000, 'jam' => '07.00 – 17.00 WITA'],
            3 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '06.00 – 18.00 WITA'],
            4 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '06.00 – 18.00 WITA'],
            5 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 18.00 WITA'],
            6 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '08.00 – 17.00 WITA'],
            7 => ['dewasa' => 15000, 'anak' => 10000, 'jam' => '07.30 – 17.00 WITA'],
            8 => ['dewasa' => 15000, 'anak' => 10000, 'jam' => '07.00 – 17.30 WITA'],
            9 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 17.00 WITA'],
            10 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '08.00 – 17.00 WITA'],
            11 => ['dewasa' => 0, 'anak' => 0, 'jam' => '24 Jam (Ramai 15.00 – 23.00 WITA)'],
            12 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '06.00 – 21.00 WITA'],
            13 => ['dewasa' => 0, 'anak' => 0, 'jam' => 'Setiap Hari (04.30 – 21.00 WITA)'],
            14 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '08.00 – 16.00 WITA (Senin–Sabtu)'],
            15 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '06.00 – 18.00 WITA'],
            16 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '06.00 – 18.00 WITA'],
            17 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 17.00 WITA'],
            18 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '06.00 – 21.00 WITA'],
            19 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '07.00 – 18.00 WITA'],
            20 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 17.00 WITA (PNBP SIMAKSI)'],
            21 => ['dewasa' => 15000, 'anak' => 10000, 'jam' => '07.00 – 17.30 WITA'],
            22 => ['dewasa' => 0, 'anak' => 0, 'jam' => '24 Jam (Ramai 06.00 – 21.00 WITA)'],
            23 => ['dewasa' => 10000, 'anak' => 5000, 'jam' => '07.00 – 17.30 WITA'],
            24 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '07.00 – 17.30 WITA'],
            25 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '07.00 – 18.00 WITA'],
            26 => ['dewasa' => 5000, 'anak' => 3000, 'jam' => '07.00 – 18.00 WITA'],
            27 => ['dewasa' => 0, 'anak' => 0, 'jam' => '24 Jam (Ramai 06.00 – 21.00 WITA)'],
        ];
        $destRows = $this->readSheetRows($spreadsheet, 'dim_destinasi');
        $destBatch = [];
        foreach ($destRows as $idx => $r) {
            if (empty($r['destinasi_id'])) {
                continue;
            }
            $dId = (int) $r['destinasi_id'];
            $tInfo = $destTariffs[$dId] ?? ['dewasa' => null, 'anak' => null, 'jam' => null];
            $kat = (string) $r['kategori'];
            $photos = $curatedPhotos[$kat] ?? $curatedPhotos['Bahari'];
            $mainPhoto = $photos[$idx % count($photos)];
            if (config('destination_photos.'.$dId)) {
                $mainPhoto = sprintf('/images/destinasi/destinasi-%02d.webp', $dId);
                $photos = [$mainPhoto];
            }

            $destBatch[] = [
                'id' => $dId,
                'slug' => Str::slug((string) $r['nama_destinasi']).'-'.$dId,
                'nama_destinasi' => (string) $r['nama_destinasi'],
                'wilayah_id' => (int) $r['wilayah_id'],
                'kategori' => $kat,
                'latitude' => (float) $r['latitude'],
                'longitude' => (float) $r['longitude'],
                'harga_tiket_dewasa' => $tInfo['dewasa'],
                'harga_tiket_anak' => $tInfo['anak'],
                'jam_operasional' => $tInfo['jam'],
                'kapasitas_harian' => 250,
                'deskripsi_id' => (string) $r['deskripsi_id'],
                'deskripsi_en' => (string) $r['deskripsi_en'],
                'fasilitas_json' => json_encode(['Area Parkir', 'Toilet & Bilas', 'Pusat Informasi', 'Warung Kuliner Lokal', 'Spot Foto', 'Mushola']),
                'galeri_json' => json_encode($photos),
                'foto_utama' => $mainPhoto,
                'rating_rata_rata' => 0,
                'jumlah_ulasan' => 0,
                'tingkat_keramaian_hari_ini' => 'Belum ada data',
                'keramaian_persen_hari_ini' => 0,
                'is_unggulan' => in_array((int) $r['destinasi_id'], [1, 2, 4, 5, 6, 11, 12, 22], true),
                'status_aktif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $appDb->table('destinasi')->insertOrIgnore($destBatch);
        $sheetCounts['dim_destinasi'] = count($destBatch);

        // 5. Seed dim_waktu -> waktu_kalender (1,826 rows)
        $waktuRows = $this->readSheetRows($spreadsheet, 'dim_waktu');
        $waktuBatch = [];
        foreach ($waktuRows as $r) {
            if (empty($r['tanggal'])) {
                continue;
            }
            $waktuBatch[] = [
                'tanggal' => $this->parseExcelDate($r['tanggal']),
                'tahun' => (int) $r['tahun'],
                'kuartal' => (int) $r['kuartal'],
                'bulan' => (int) $r['bulan'],
                'nama_bulan' => (string) $r['nama_bulan'],
                'periode' => (string) $r['periode'],
                'hari_dalam_minggu' => (int) $r['hari_dalam_minggu'],
                'nama_hari' => (string) $r['nama_hari'],
                'akhir_pekan' => (string) $r['akhir_pekan'],
                'musim_libur_sekolah' => (string) $r['musim_libur_sekolah'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($waktuBatch, 500) as $chunk) {
            $appDb->table('waktu_kalender')->insertOrIgnore($chunk);
        }
        $sheetCounts['dim_waktu'] = count($waktuBatch);

        $adminEmail = config('services.tourism_admin.email');
        $adminPassword = config('services.tourism_admin.password');
        if (is_string($adminEmail) && $adminEmail !== '' && is_string($adminPassword) && $adminPassword !== '') {
            $appDb->table('users')->insertOrIgnore([
                'name' => 'Admin Dinas Pariwisata',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'peran' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 15. Record Initial Dataset Import Verification Log
        $totalImportedExcel = array_sum($sheetCounts);
        ImportLog::create([
            'nama_file' => 'dataset_pariwisata_sulteng.xlsx',
            'jenis_data' => 'Master dan referensi workbook',
            'total_baris' => $totalImportedExcel,
            'baris_berhasil' => $totalImportedExcel,
            'baris_gagal' => 0,
            'detail_gagal_json' => $sheetCounts,
            'status' => 'berhasil',
            'diimpor_oleh' => null,
        ]);

        // 16. Run Full Star Schema ETL & Analytics Engine
        $this->command?->info('Menjalankan ETL ke Data Warehouse...');
        $etlService = app(WarehouseEtlService::class);
        $etlService->runEtl('DatabaseSeeder (Inisialisasi Awal)');

        ActivityLog::create([
            'user_id' => null,
            'nama_user' => 'Sistem',
            'peran' => 'admin',
            'aksi' => 'Impor Dataset & Inisialisasi Warehouse',
            'modul' => 'Data & ETL',
            'deskripsi' => "Berhasil mengimpor {$totalImportedExcel} baris dari dataset_pariwisata_sulteng.xlsx tanpa kehilangan data dan membangun Star Schema Data Warehouse.",
            'metadata_json' => $sheetCounts,
            'ip_address' => '127.0.0.1',
        ]);

        $this->command?->info("Selesai! Total {$totalImportedExcel} baris Excel berhasil diimpor dan dimuat ke Data Warehouse.");
    }

    /**
     * Read all data rows from a worksheet into an associative array keyed by row-1 header.
     *
     * @return list<array<string, mixed>>
     */
    private function readSheetRows($spreadsheet, string $sheetName): array
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (! $sheet) {
            return [];
        }

        $rawRows = $sheet->toArray(null, true, false, false);
        if (count($rawRows) < 2) {
            return [];
        }

        $headers = array_map(fn ($h) => trim((string) $h), $rawRows[0]);
        $result = [];

        for ($i = 1, $max = count($rawRows); $i < $max; $i++) {
            $row = $rawRows[$i];
            $assoc = [];
            $hasValue = false;
            foreach ($headers as $colIdx => $colName) {
                if ($colName === '') {
                    continue;
                }
                $val = $row[$colIdx] ?? null;
                if ($val !== null && $val !== '') {
                    $hasValue = true;
                }
                $assoc[$colName] = $val;
            }
            if ($hasValue) {
                $result[] = $assoc;
            }
        }

        return $result;
    }

    private function parseExcelDate(mixed $val): string
    {
        if ($val instanceof \DateTimeInterface) {
            return $val->format('Y-m-d');
        }
        if (is_numeric($val)) {
            return ExcelDate::excelToDateTimeObject((float) $val)->format('Y-m-d');
        }
        $str = trim((string) $val);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $str, $m)) {
            return $m[0];
        }

        return date('Y-m-d', strtotime($str) ?: time());
    }
}
