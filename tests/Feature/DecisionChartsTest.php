<?php

namespace Tests\Feature;

use App\Services\DashboardChartService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DecisionChartsTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'warehouse'];

    public function test_booking_charts_keep_denominators_checkin_evidence_and_cohorts_distinct(): void
    {
        $template = (array) DB::table('tiket')->first();
        $nextId = (int) DB::table('tiket')->max('id') + 1;
        $cases = [
            ['a', '2023-01-01', '2023-01-05', 'Selesai', null],
            ['a', '2023-02-01', '2023-02-02', 'Sudah Check-In', '2023-02-02 09:00:00'],
            ['b', '2023-01-02', '2023-01-05', 'Dibatalkan', null],
            ['c', '2023-01-03', now()->addYear()->toDateString(), 'Selesai', null],
            ['d', '2023-01-04', '2023-01-03', 'Selesai', null],
        ];
        foreach ($cases as $index => [$identity, $booked, $visit, $status, $checkin]) {
            DB::table('tiket')->insert(array_replace($template, [
                'id' => $nextId + $index,
                'kode_tiket' => 'TEST-DECISION-'.$index,
                'user_id' => null,
                'kode_pengguna' => 'TEST-DECISION-'.$identity,
                'destinasi_id' => 1,
                'tanggal_pesan' => $booked,
                'tanggal_kunjungan' => $visit,
                'status' => $status,
                'waktu_checkin' => $checkin,
            ]));
        }
        $charts = app(DashboardChartService::class)->getDecisionCharts(['tahun' => '2023', 'destinasi_id' => 1]);
        $cancelled = collect($charts['chart_33_pembatalan'])->first();
        $this->assertSame(5, (int) $cancelled->total_pemesanan);
        $this->assertEquals(20, $cancelled->persen_batal);
        $this->assertEquals(3, collect($charts['chart_32_jarak_pemesanan'])->sum('jumlah_pemesanan'));
        $scanned = collect($charts['chart_34_verifikasi_tiket'])->first();
        $this->assertSame(2, (int) $scanned->tiket_layak);
        $this->assertSame(1, (int) $scanned->checkin_tercatat);
        $this->assertSame(1, $scanned->belum_terverifikasi);
        $cohort = collect($charts['chart_36_pemesanan_ulang'])->firstWhere('cohort', '2023-01');
        $this->assertSame(2, $cohort['jumlah_pemesan']);
        $this->assertSame(50.0, $cohort['retensi'][0]['persen']);
        $this->assertSame(1, $cohort['retensi'][0]['jumlah_kembali']);
    }

    public function test_capacity_uses_strict_excess_and_excludes_unknown_capacity(): void
    {
        $warehouse = DB::connection('warehouse');
        $template = ['wilayah_id' => 1, 'kategori' => 'Bahari', 'tingkat_keramaian' => 'Ramai'];
        $nextId = (int) $warehouse->table('fact_kunjungan_harian')->max('harian_id') + 1;
        foreach ([[101, 100], [100, 100], [500, 0]] as $index => [$visitors, $capacity]) {
            $warehouse->table('fact_kunjungan_harian')->insert(array_replace($template, [
                'harian_id' => $nextId + $index,
                'destinasi_id' => 1,
                'tanggal' => '2023-01-0'.($index + 1),
                'total_pengunjung' => $visitors,
                'kapasitas_harian' => $capacity,
            ]));
        }
        $charts = app(DashboardChartService::class)->getDecisionCharts(['tahun' => '2023', 'destinasi_id' => 1]);
        $capacity = collect($charts['chart_35_kelebihan_kapasitas'])->first();
        $this->assertSame(2, (int) $capacity->hari_tercatat);
        $this->assertSame(1, (int) $capacity->hari_melebihi);
        $this->assertEquals(50, $capacity->persen_melebihi);
    }

    public function test_new_charts_follow_filters_and_unobserved_cohort_months_stay_unknown(): void
    {
        $service = app(DashboardChartService::class);
        $charts = $service->getDecisionCharts(['tahun' => '2026', 'destinasi_id' => 1]);
        $region = DB::connection('warehouse')->table('dim_destinasi')->where('destinasi_id', 1)->value('wilayah_id');
        foreach ($charts['chart_31_lama_menginap'] as $row) {
            $this->assertEquals($region, $row->wilayah_id);
            $this->assertGreaterThan(0, $row->rata_hari);
        }
        foreach (['chart_33_pembatalan', 'chart_34_verifikasi_tiket', 'chart_35_kelebihan_kapasitas'] as $key) {
            foreach ($charts[$key] as $row) {
                $this->assertSame(1, (int) $row->destinasi_id);
            }
        }
        foreach ($charts['chart_36_pemesanan_ulang'] as $cohort) {
            foreach ($cohort['retensi'] as $point) {
                $month = CarbonImmutable::parse($cohort['cohort'].'-01')->addMonths($point['bulan_ke'])->format('Y-m');
                if ($month > $charts['period']['end']) {
                    $this->assertNull($point['persen']);
                    $this->assertNull($point['jumlah_kembali']);
                }
            }
        }
        $empty = $service->getDecisionCharts(['kategori' => 'Tidak ada kategori ini']);
        $this->assertEmpty($empty['chart_33_pembatalan']);
        $this->assertEmpty($empty['chart_35_kelebihan_kapasitas']);
        $this->assertSame(0, collect($empty['chart_32_jarak_pemesanan'])->sum('jumlah_pemesanan'));
    }
}
