<?php

namespace Tests\Feature;

use App\Models\Itinerary;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'warehouse'];

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('mysql')->table('destinasi')->whereIn('id', [1, 2])
            ->update(['harga_tiket_dewasa' => 20000, 'harga_tiket_anak' => 10000]);
    }

    public function test_individual_booking_check_in_and_cancellation_keep_both_databases_consistent(): void
    {
        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $manager = User::where('email', 'pengelola.togean@pariwisata-sulteng.test')->firstOrFail();

        $this->actingAs($tourist)->post('/tiket/pesan', [
            'destinasi_id' => $manager->destinasi_id,
            'tanggal_kunjungan' => now()->toDateString(),
            'jumlah_dewasa' => 2,
            'jumlah_anak' => 1,
            'metode_bayar' => 'QRIS',
        ])->assertSessionHasNoErrors()->assertRedirect(route('tiket.riwayat'));

        $ticket = Tiket::where('user_id', $tourist->id)->latest('id')->firstOrFail();
        $this->assertSame(2 * $ticket->harga_dewasa + $ticket->harga_anak, $ticket->total_bayar_rp);
        $this->assertDatabaseHas('fact_tiket', ['tiket_id' => $ticket->id, 'status' => 'Selesai'], 'warehouse');

        $this->actingAs($manager)->post('/pengelola/verifikasi-tiket', [
            'kode_tiket' => $ticket->kode_tiket,
        ])->assertSessionHas('success');
        $this->assertSame('Sudah Check-In', $ticket->fresh()->status);
        $this->assertDatabaseHas('fact_tiket', ['tiket_id' => $ticket->id, 'status' => 'Sudah Check-In'], 'warehouse');

        $this->actingAs($tourist)->post('/tiket/'.$ticket->id.'/batal')->assertSessionHas('error');
        $this->assertSame('Sudah Check-In', $ticket->fresh()->status);
    }

    public function test_cancelled_ticket_cannot_be_checked_in(): void
    {
        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $manager = User::where('email', 'pengelola.togean@pariwisata-sulteng.test')->firstOrFail();
        $this->actingAs($tourist)->post('/tiket/pesan', [
            'destinasi_id' => $manager->destinasi_id,
            'tanggal_kunjungan' => now()->toDateString(),
            'jumlah_dewasa' => 1,
            'jumlah_anak' => 0,
            'metode_bayar' => 'Bayar di Loket',
        ])->assertSessionHasNoErrors();
        $ticket = Tiket::where('user_id', $tourist->id)->latest('id')->firstOrFail();

        $this->post('/tiket/'.$ticket->id.'/batal')->assertSessionHas('info');
        $this->assertDatabaseHas('fact_tiket', ['tiket_id' => $ticket->id, 'status' => 'Dibatalkan'], 'warehouse');
        $this->actingAs($manager)->post('/pengelola/verifikasi-tiket', [
            'kode_tiket' => $ticket->kode_tiket,
        ])->assertSessionHas('error');
        $this->assertSame('Dibatalkan', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->waktu_checkin);
    }

    public function test_booking_from_an_itinerary_syncs_every_ticket_to_the_warehouse(): void
    {
        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $start = now()->addDays(2)->startOfDay();
        $this->actingAs($tourist)->post('/itinerari', [
            'judul_perjalanan' => 'Uji alur tiket itinerari',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $start->copy()->addDay()->toDateString(),
            'jumlah_hari' => 2,
            'jumlah_dewasa' => 2,
            'jumlah_anak' => 0,
            'items' => [
                ['destinasi_id' => 1, 'hari_ke' => 1, 'urutan' => 1, 'jam_mulai' => '09:00', 'durasi_menit' => 120],
                ['destinasi_id' => 2, 'hari_ke' => 2, 'urutan' => 2, 'jam_mulai' => '09:00', 'durasi_menit' => 120],
            ],
        ])->assertSessionHasNoErrors();
        $itinerary = Itinerary::where('user_id', $tourist->id)->latest('id')->firstOrFail();
        $lastTicketId = (int) Tiket::max('id');
        $this->post('/itinerari/'.$itinerary->id.'/pesan-semua')
            ->assertSessionHasNoErrors()->assertRedirect(route('tiket.riwayat'));

        $tickets = Tiket::where('id', '>', $lastTicketId)->orderBy('tanggal_kunjungan')->get();
        $this->assertCount(2, $tickets);
        $this->assertSame($start->toDateString(), $tickets[0]->tanggal_kunjungan->toDateString());
        $this->assertSame($start->copy()->addDay()->toDateString(), $tickets[1]->tanggal_kunjungan->toDateString());
        $this->assertSame($itinerary->estimasi_biaya_tiket_rp, (int) $tickets->sum('total_bayar_rp'));
        foreach ($tickets as $ticket) {
            $this->assertDatabaseHas('fact_tiket', [
                'tiket_id' => $ticket->id,
                'kode_tiket' => $ticket->kode_tiket,
                'status' => 'Selesai',
                'total_bayar_rp' => $ticket->total_bayar_rp,
            ], 'warehouse');
        }
        $this->assertSame(2, DB::connection('warehouse')->table('fact_tiket')->whereIn('tiket_id', $tickets->pluck('id'))->count());
    }

    public function test_failed_batch_sync_rolls_back_tickets_already_created_in_both_databases(): void
    {
        $tourist = User::where('email', 'wisatawan@pariwisata-sulteng.test')->firstOrFail();
        $this->actingAs($tourist)->post('/itinerari', [
            'judul_perjalanan' => 'Uji pembatalan transaksi gagal',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jumlah_hari' => 1,
            'jumlah_dewasa' => 1,
            'jumlah_anak' => 0,
            'items' => [
                ['destinasi_id' => 1, 'hari_ke' => 1, 'urutan' => 1, 'jam_mulai' => '09:00'],
                ['destinasi_id' => 2, 'hari_ke' => 1, 'urutan' => 2, 'jam_mulai' => '13:00'],
            ],
        ])->assertSessionHasNoErrors();
        $itinerary = Itinerary::where('user_id', $tourist->id)->latest('id')->firstOrFail();
        $appCount = Tiket::count();
        $warehouse = DB::connection('warehouse');
        $warehouseCount = $warehouse->table('fact_tiket')->count();
        $attempts = 0;
        $warehouse->beforeExecuting(function (string $query) use (&$attempts): void {
            if (str_starts_with(strtolower($query), 'insert into') && str_contains($query, 'fact_tiket')) {
                $attempts++;
                if ($attempts === 2) {
                    throw new \RuntimeException('Simulated warehouse failure');
                }
            }
        });

        $this->withoutExceptionHandling();
        try {
            $this->post('/itinerari/'.$itinerary->id.'/pesan-semua');
            $this->fail('The simulated warehouse failure must stop ticket issuance.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated warehouse failure', $exception->getMessage());
        }
        $this->assertSame(2, $attempts);
        $this->assertSame($appCount, Tiket::count());
        $this->assertSame($warehouseCount, $warehouse->table('fact_tiket')->count());
    }
}
