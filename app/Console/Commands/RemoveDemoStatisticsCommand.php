<?php

namespace App\Console\Commands;

use App\Services\WarehouseEtlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RemoveDemoStatisticsCommand extends Command
{
    protected $signature = 'tourism:remove-demo-statistics {--apply : Cadangkan dan hapus data simulasi}';

    protected $description = 'Bersihkan statistik simulasi berdasarkan penanda sumber impor awal';

    public function handle(WarehouseEtlService $etlService): int
    {
        $operational = DB::connection('mysql');
        $warehouse = DB::connection('warehouse');
        $sources = [
            'kunjungan_bulanan' => ['excel_original'],
            'kunjungan_harian' => ['excel_original', 'supplementary'],
            'hotel_bulanan' => ['excel_original'],
            'asal_pengunjung' => ['excel_original'],
            'ulasan' => ['excel_original'],
            'tiket' => ['excel_original'],
            'funnel_bulanan' => ['excel_original'],
        ];
        $derivedTables = ['hasil_prediksi', 'hasil_sentimen', 'skor_destinasi', 'rekomendasi_aksi'];
        $workbook = IOFactory::load(base_path('dataset_pariwisata_sulteng.xlsx'));
        $sheet = $workbook->getSheetByName('dim_destinasi')->toArray(formatData: false);
        $headers = array_shift($sheet);
        $catalog = array_map(fn (array $row): array => array_combine($headers, $row), $sheet);
        $fields = ['harga_tiket_dewasa', 'harga_tiket_anak', 'jam_operasional'];

        foreach ($sources as $table => $labels) {
            $count = $operational->table($table)->whereIn('sumber_data', $labels)->count();
            $this->line("{$table}: {$count}");
        }

        if (! $this->option('apply')) {
            return self::SUCCESS;
        }

        $backupPath = storage_path('app/demo-statistics-'.now()->format('Ymd-His-u').'.json');
        $operational->transaction(function () use ($operational, $warehouse, $sources, $derivedTables, $backupPath, $catalog, $fields): void {
            $backup = ['operational' => [], 'warehouse' => []];
            $catalogChanges = [];
            foreach ($catalog as $source) {
                $destination = $operational->table('destinasi')->where('id', $source['destinasi_id'])->lockForUpdate()->first();
                if ($destination === null || $destination->nama_destinasi !== $source['nama_destinasi']) {
                    continue;
                }
                foreach ($fields as $field) {
                    if ($destination->{$field} !== null && (string) $destination->{$field} === (string) $source[$field]) {
                        $catalogChanges[$destination->id][$field] = null;
                        $backup['operational']['atribut_destinasi'][$destination->id][$field] = $destination->{$field};
                    }
                }
            }
            foreach ($sources as $table => $labels) {
                $backup['operational'][$table] = $operational->table($table)->whereIn('sumber_data', $labels)->lockForUpdate()->get();
            }
            foreach ($derivedTables as $table) {
                $backup['warehouse'][$table] = $warehouse->table($table)->get();
            }

            $contents = json_encode($backup, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            if (file_put_contents($backupPath, $contents, LOCK_EX) !== strlen($contents)) {
                throw new \RuntimeException('Cadangan data belum berhasil ditulis.');
            }

            foreach ($sources as $table => $labels) {
                $operational->table($table)->whereIn('sumber_data', $labels)->delete();
            }
            foreach ($catalogChanges as $destinationId => $attributes) {
                $operational->table('destinasi')->where('id', $destinationId)->update($attributes);
            }
        });

        $etlService->runEtl('Pembersihan statistik simulasi');
        $warehouse->transaction(function () use ($warehouse, $derivedTables): void {
            foreach ($derivedTables as $table) {
                $warehouse->table($table)->delete();
            }
        });

        $this->info('Data simulasi dibersihkan dan warehouse disinkronkan.');
        $this->line("Cadangan lokal: {$backupPath}");

        return self::SUCCESS;
    }
}
