<?php

namespace Tests\Feature;

use App\Models\Destinasi;
use App\Models\User;
use App\Services\DashboardChartService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JelajahSultengSystemTest extends TestCase
{
    public function test_official_dataset_totals_and_operational_data_exclude_demo_imports(): void
    {
        $appDb = DB::connection('mysql');
        $dw = DB::connection('warehouse');

        $this->assertEquals(13, $appDb->table('wilayah')->count());
        $this->assertEquals(13, $dw->table('dim_wilayah')->count());

        $this->assertEquals(27, $appDb->table('destinasi')->count());
        $this->assertEquals(27, $dw->table('dim_destinasi')->count());

        $this->assertEquals(1826, $appDb->table('waktu_kalender')->count());
        $this->assertEquals(1826, $dw->table('dim_waktu')->count());

        $this->assertGreaterThanOrEqual(5, $appDb->table('users')->count());

        foreach (['kunjungan_bulanan', 'kunjungan_harian', 'hotel_bulanan', 'asal_pengunjung', 'ulasan', 'tiket', 'funnel_bulanan'] as $table) {
            $this->assertSame(0, $appDb->table($table)->whereIn('sumber_data', ['excel_original', 'supplementary'])->count());
        }

        $dataset = file(database_path('pariwisata_resmi_2025.csv'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(313, $dataset);
        $totals = [];
        $blankCount = 0;
        foreach (array_slice($dataset, 1) as $line) {
            [$metric, , , $value] = str_getcsv($line, escape: '');
            $totals[$metric] = ($totals[$metric] ?? 0) + (int) $value;
            $blankCount += (int) ($value === '');
        }
        $this->assertSame(['perjalanan_wisnus' => 11668933, 'kunjungan_wisman' => 28165], $totals);
        $this->assertSame(1, $blankCount);
    }

    public function test_operational_chart_services_preserve_their_payloads(): void
    {
        /** @var DashboardChartService $chartService */
        $chartService = app(DashboardChartService::class);

        $publicCharts = $chartService->getPublicCharts(['compare_ids' => [1, 5, 11], 'destinasi_id' => 1]);
        $this->assertCount(8, $publicCharts);

        $managerCharts = $chartService->getManagerCharts(1);
        $this->assertCount(6, $managerCharts);

        $adminCharts = $chartService->getAdminCharts(['tahun' => '12BLN']);
        $this->assertCount(16, $adminCharts);
    }

    public function test_public_portal_pages_and_chatbot_respond_ok(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/destinasi')->assertStatus(200);

        $firstDest = Destinasi::first();
        // Viewing destination detail is publicly accessible for guests
        $this->get('/destinasi/'.$firstDest->slug)->assertStatus(200);

        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $this->actingAs($tourist)->get('/destinasi/'.$firstDest->slug)->assertStatus(200);

        $this->get('/peta')->assertStatus(200);

        // Guest access to /statistik is restricted and redirects to login
        $this->get('/statistik')->assertRedirect('/login');

        // Admin has authorized access to statistical BI
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();
        $this->actingAs($admin)->get('/statistik')->assertStatus(200);

        $this->get('/informasi')->assertStatus(200);

        $chatResponse = $this->postJson('/chatbot/ask', [
            'message' => 'Rekomendasi wisata bahari murah di Togean atau Palu',
            'locale' => 'id',
        ]);
        $chatResponse->assertStatus(200)
            ->assertJsonStructure(['reply', 'destinations']);
    }

    public function test_role_based_access_and_dashboards(): void
    {
        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $manager = User::where('email', 'pengelola.togean@pariwisata-sulteng.test')->firstOrFail();
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();

        // Tourist can access wishlist, itinerary, tickets, but cannot access admin dashboard
        $this->actingAs($tourist)->get('/wishlist')->assertStatus(200);
        $this->actingAs($tourist)->get('/itinerari')->assertStatus(200);
        $this->actingAs($tourist)->get('/tiket')->assertStatus(200);
        $this->actingAs($tourist)->get('/admin/dashboard')->assertStatus(403);

        // Manager can access Pengelola dashboard, but cannot access Admin dashboard
        $this->actingAs($manager)->get('/pengelola/dashboard')->assertStatus(200);
        $this->actingAs($manager)->get('/admin/dashboard')->assertStatus(403);

        // Admin can access both Admin dashboard and Pengelola dashboard
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/pengelola/dashboard')->assertStatus(200);

        // Switching accounts directly from an active session works without guest middleware blocking
        $this->actingAs($manager)->get('/login')->assertStatus(200);
        $this->actingAs($manager)->post('/login', [
            'email' => 'admin@pariwisata-sulteng.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        // Verify all 27 destinations now have their own dedicated Pengelola Destinasi account
        $this->assertEquals(27, User::where('peran', 'pengelola_destinasi')->count());
    }
}

