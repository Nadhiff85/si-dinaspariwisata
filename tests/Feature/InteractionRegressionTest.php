<?php

namespace Tests\Feature;

use App\Models\KunjunganHarian;
use App\Models\User;
use App\Services\DashboardChartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InteractionRegressionTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'warehouse'];

    public function test_latest_reporting_year_compares_the_same_available_months(): void
    {
        $latest = DB::connection('warehouse')->table('fact_kunjungan_bulanan')->max('periode');
        $latest ??= now()->format('Y-m');
        $period = app(DashboardChartService::class)->getReportingPeriod(substr($latest, 0, 4));

        $this->assertSame($latest, $period['end']);
        $this->assertSame(substr($latest, 5, 2), substr($period['previous_end'], 5, 2));
    }

    public function test_chatbot_returns_no_matches_instead_of_unrelated_destinations(): void
    {
        $this->postJson('/chatbot/ask', [
            'message' => 'museum di Poso tiket di bawah 1 ribu',
            'locale' => 'id',
        ])->assertOk()
            ->assertJsonCount(0, 'destinations')
            ->assertJsonPath('reply', fn (string $reply): bool => str_contains($reply, 'Belum ada destinasi')
                && str_contains($reply, 'Budaya & Sejarah')
                && str_contains($reply, '1.000'));
    }

    public function test_chatbot_honours_category_explicit_budget_and_free_admission(): void
    {
        DB::table('destinasi')->where('kategori', 'Bahari')->where('status_aktif', true)
            ->orderBy('id')->limit(1)->update(['harga_tiket_dewasa' => 0, 'harga_tiket_anak' => 0]);

        $destinations = $this->postJson('/chatbot/ask', [
            'message' => 'wisata bahari murah',
        ])->assertOk()->json('destinations');

        $this->assertNotEmpty($destinations);
        foreach ($destinations as $destination) {
            $this->assertSame('Bahari', $destination['kategori']);
            $this->assertLessThanOrEqual(15000, $destination['harga_dewasa']);
        }

        foreach (['murah di bawah 1 ribu' => 1000, 'free admission' => 0] as $message => $budget) {
            $response = $this->postJson('/chatbot/ask', ['message' => $message, 'locale' => 'en'])->assertOk();
            foreach ($response->json('destinations') as $destination) {
                $this->assertLessThanOrEqual($budget, $destination['harga_dewasa']);
            }
            $this->assertStringContainsString(number_format($budget), $response->json('reply'));
        }
    }

    public function test_admin_historical_charts_share_the_selected_reporting_period(): void
    {
        $service = app(DashboardChartService::class);
        $charts = $service->getAdminCharts(['tahun' => '2025']);
        $expected = (int) DB::connection('warehouse')->table('fact_kunjungan_bulanan')
            ->whereBetween('periode', ['2025-01', '2025-12'])->sum('total_pengunjung');

        foreach (['chart_14_wisnus_wisman_area', 'chart_20_tpk_kunjungan_combo', 'chart_25_tren_rating_bulanan'] as $key) {
            foreach ($charts[$key] as $row) {
                $this->assertStringStartsWith('2025-', is_array($row) ? $row['periode'] : $row->periode);
            }
        }
        $this->assertSame($expected, (int) $charts['chart_14_wisnus_wisman_area']->sum('total'));
        $this->assertSame($expected, (int) $charts['chart_15_peta_kunjungan_wilayah']->sum('total_pengunjung'));
        $this->assertSame($expected, (int) $charts['chart_16_treemap_kontribusi']->sum('y'));
        $this->assertSame($expected > 0 ? ['2025'] : [], array_column($charts['chart_19_musiman_heatmap'], 'name'));
    }

    public function test_admin_kpis_follow_year_and_destination_filters(): void
    {
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();
        $this->actingAs($admin);

        foreach (['tahun=2025', 'tahun=2024&destinasi_id=1'] as $query) {
            parse_str($query, $filters);
            $page = $this->get('/admin/dashboard?'.$query)->assertOk()->viewData('page');
            $expected = DB::connection('warehouse')->table('fact_kunjungan_bulanan')
                ->whereBetween('periode', [$filters['tahun'].'-01', $filters['tahun'].'-12']);
            if (isset($filters['destinasi_id'])) {
                $expected->where('destinasi_id', $filters['destinasi_id']);
            }
            $this->assertSame((int) $expected->sum('total_pengunjung'), $page['props']['kpi']['total_kunjungan_12bln']);
            $this->assertSame($filters['tahun'].'-01', $page['props']['reportingPeriod']['start']);
            $this->assertSame($filters['tahun'].'-12', $page['props']['reportingPeriod']['end']);
        }

        $page = $this->get('/admin/dashboard?tahun=ALL')->assertOk()->viewData('page');
        $this->assertNull($page['props']['kpi']['growth_kunjungan_persen']);
    }

    public function test_manager_reports_the_actual_dates_of_the_latest_records(): void
    {
        $manager = User::where('email', 'pengelola.togean@pariwisata-sulteng.test')->firstOrFail();
        $page = $this->actingAs($manager)->get('/pengelola/dashboard')->assertOk()->viewData('page');
        $latestDay = KunjunganHarian::where('destinasi_id', $manager->destinasi_id)->max('tanggal');
        $latestMonth = DB::connection('warehouse')->table('fact_kunjungan_bulanan')
            ->where('destinasi_id', $manager->destinasi_id)->max('periode');

        $this->assertSame($latestDay, $page['props']['kpi']['tanggal_laporan']);
        $this->assertSame($latestMonth, $page['props']['kpi']['periode_bulanan']);

        $slug = DB::table('destinasi')->where('id', $manager->destinasi_id)->value('slug');
        $detail = $this->get('/destinasi/'.$slug)->assertOk()->viewData('page');
        $latestWarehouseDate = DB::connection('warehouse')->table('fact_kunjungan_harian')
            ->where('destinasi_id', $manager->destinasi_id)->max('tanggal');
        $this->assertSame($latestWarehouseDate, $detail['props']['laporanKeramaian']?->tanggal);
    }
}
