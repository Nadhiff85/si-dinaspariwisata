<?php

namespace App\Console\Commands;

use App\Services\WarehouseEtlService;
use Illuminate\Console\Command;

class RunWarehouseEtlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'etl:run {--models-only : Jalankan ulang hanya pipeline model analitik}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan sinkronisasi ETL dari database operasional ke Data Warehouse Star Schema ';

    /**
     * Execute the console command.
     */
    public function handle(WarehouseEtlService $etlService): int
    {
        if ($this->option('models-only')) {
            $this->warn('Model lama dinonaktifkan. Statistik resmi tersedia pada dashboard.');

            return self::FAILURE;
        }

        $etlRes = $etlService->runEtl('Artisan CLI / Scheduler');
        $this->info("ETL selesai: {$etlRes['total_rows']} baris diproses dalam {$etlRes['duration']} detik.");

        return self::SUCCESS;
    }
}

