<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Destinasi;
use App\Models\Itinerary;
use App\Models\Tiket;
use App\Models\Ulasan;
use App\Models\Wishlist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TouristActionController extends Controller
{
    public function wishlistIndex(Request $request): Response
    {
        $wishlists = Wishlist::query()
            ->with('destinasi.wilayah')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Wishlist/Index', [
            'wishlists' => $wishlists,
        ]);
    }

    public function toggleWishlist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $existing = Wishlist::where('user_id', $user->id)
            ->where('destinasi_id', $validated['destinasi_id'])
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('info', 'Destinasi dihapus dari daftar Wishlist Anda.');
        }

        Wishlist::create([
            'user_id' => $user->id,
            'destinasi_id' => $validated['destinasi_id'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return back()->with('success', 'Destinasi berhasil disimpan ke daftar Wishlist Anda!');
    }

    public function itinerariIndex(Request $request): Response
    {
        $user = $request->user();

        $itineraries = Itinerary::query()
            ->with(['items.destinasi.wilayah'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $wishlistDestinations = Wishlist::query()
            ->with('destinasi.wilayah')
            ->where('user_id', $user->id)
            ->get()
            ->pluck('destinasi')
            ->filter();

        $allDestinations = Destinasi::query()
            ->with('wilayah:id,nama_wilayah')
            ->where('status_aktif', true)
            ->orderBy('nama_destinasi')
            ->get();

        return Inertia::render('Itinerari/Index', [
            'itineraries' => $itineraries,
            'wishlistDestinations' => $wishlistDestinations->values(),
            'allDestinations' => $allDestinations,
        ]);
    }

    public function storeItinerari(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer'],
            'judul_perjalanan' => ['required', 'string', 'max:160'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jumlah_hari' => ['required', 'integer', 'min:1', 'max:14'],
            'jumlah_dewasa' => ['required', 'integer', 'min:1', 'max:50'],
            'jumlah_anak' => ['required', 'integer', 'min:0', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.destinasi_id' => ['required', 'exists:destinasi,id'],
            'items.*.hari_ke' => ['required', 'integer', 'min:1'],
            'items.*.urutan' => ['required', 'integer', 'min:1'],
            'items.*.jam_mulai' => ['required', 'string', 'max:20'],
            'items.*.durasi_menit' => ['nullable', 'integer', 'min:30'],
            'items.*.catatan_item' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($validated, $user) {
            $destMap = Destinasi::whereIn('id', collect($validated['items'])->pluck('destinasi_id'))
                ->get()
                ->keyBy('id');

            $totalBiaya = 0;
            $preparedItems = [];

            foreach ($validated['items'] as $idx => $item) {
                $d = $destMap->get($item['destinasi_id']);
                $biayaItem = $d
                    ? (($d->harga_tiket_dewasa * $validated['jumlah_dewasa']) + ($d->harga_tiket_anak * $validated['jumlah_anak']))
                    : 0;
                $totalBiaya += $biayaItem;

                $preparedItems[] = [
                    'destinasi_id' => (int) $item['destinasi_id'],
                    'hari_ke' => (int) $item['hari_ke'],
                    'urutan' => (int) ($item['urutan'] ?? ($idx + 1)),
                    'jam_mulai' => $item['jam_mulai'],
                    'durasi_menit' => (int) ($item['durasi_menit'] ?? 120),
                    'estimasi_biaya_rp' => $biayaItem,
                    'catatan_item' => $item['catatan_item'] ?? null,
                ];
            }

            if (! empty($validated['id'])) {
                $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($validated['id']);
                $itinerary->update([
                    'judul_perjalanan' => $validated['judul_perjalanan'],
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'jumlah_hari' => $validated['jumlah_hari'],
                    'jumlah_dewasa' => $validated['jumlah_dewasa'],
                    'jumlah_anak' => $validated['jumlah_anak'],
                    'estimasi_biaya_tiket_rp' => $totalBiaya,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
                $itinerary->items()->delete();
            } else {
                $itinerary = Itinerary::create([
                    'user_id' => $user->id,
                    'judul_perjalanan' => $validated['judul_perjalanan'],
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'jumlah_hari' => $validated['jumlah_hari'],
                    'jumlah_dewasa' => $validated['jumlah_dewasa'],
                    'jumlah_anak' => $validated['jumlah_anak'],
                    'estimasi_biaya_tiket_rp' => $totalBiaya,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            }

            foreach ($preparedItems as $pi) {
                $itinerary->items()->create($pi);
            }
        });

        return back()->with('success', 'Rencana perjalanan (Itinerari) berhasil disimpan beserta kalkulasi biaya tiketnya.');
    }

    public function deleteItinerari(Request $request, int $id): RedirectResponse
    {
        $itinerary = Itinerary::where('user_id', $request->user()->id)->findOrFail($id);
        $itinerary->delete();

        return back()->with('info', 'Itinerari telah dihapus.');
    }

    public function exportItinerariPdf(Request $request, int $id): HttpResponse
    {
        $itinerary = Itinerary::query()
            ->with(['items.destinasi.wilayah', 'user'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.itinerari', [
            'itinerary' => $itinerary,
        ]);

        return $pdf->download('Itinerari-Jelajah-Sulteng-'.$itinerary->id.'.pdf');
    }

    public function pesanSemuaTiketItinerari(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::with('items.destinasi')->where('user_id', $user->id)->findOrFail($id);

        if ($itinerary->items->contains(fn ($item): bool => $item->destinasi !== null
            && ($item->destinasi->harga_tiket_dewasa === null || $item->destinasi->harga_tiket_anak === null))) {
            return back()->with('error', 'Tarif salah satu destinasi belum tersedia.');
        }

        $createdCount = 0;
        DB::connection('warehouse')->transaction(function () use ($itinerary, $user, &$createdCount) {
            DB::transaction(function () use ($itinerary, $user, &$createdCount) {
                $nextNumber = Tiket::max('id') + 1;
                foreach ($itinerary->items as $item) {
                    $dest = $item->destinasi;
                    if (! $dest) {
                        continue;
                    }
                    $visitDate = date('Y-m-d', strtotime($itinerary->tanggal_mulai.' + '.($item->hari_ke - 1).' days'));
                    $kode = 'TKT-'.str_pad((string) ($nextNumber + $createdCount), 6, '0', STR_PAD_LEFT);
                    $totalRp = ($dest->harga_tiket_dewasa * $itinerary->jumlah_dewasa) + ($dest->harga_tiket_anak * $itinerary->jumlah_anak);

                    $tiket = Tiket::create([
                        'kode_tiket' => $kode,
                        'destinasi_id' => $dest->id,
                        'user_id' => $user->id,
                        'kode_pengguna' => $user->kode_pengguna ?? 'U'.$user->id,
                        'nama_pemesan' => $user->name,
                        'email_pemesan' => $user->email,
                        'tanggal_pesan' => now()->toDateString(),
                        'tanggal_kunjungan' => $visitDate,
                        'jumlah_dewasa' => $itinerary->jumlah_dewasa,
                        'jumlah_anak' => $itinerary->jumlah_anak,
                        'harga_dewasa' => $dest->harga_tiket_dewasa,
                        'harga_anak' => $dest->harga_tiket_anak,
                        'total_bayar_rp' => $totalRp,
                        'metode_bayar' => 'QRIS',
                        'status' => 'Selesai',
                        'asal_pemesan' => $user->bahasa === 'en' ? 'Wisman' : 'Wisnus',
                        'sumber_data' => 'aplikasi_web',
                    ]);
                    $this->syncTicketToWarehouse($tiket, $dest);
                    $createdCount++;
                }
            });
        });

        return redirect()->route('tiket.riwayat')->with('success', "Berhasil menerbitkan {$createdCount} E-Tiket QR dari rencana perjalanan '{$itinerary->judul_perjalanan}'!");
    }

    public function riwayatTiket(Request $request): Response
    {
        $user = $request->user();

        $tiketList = Tiket::query()
            ->with('destinasi.wilayah')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->kode_pengguna) {
                    $q->orWhere('kode_pengguna', $user->kode_pengguna);
                }
            })
            ->orderByDesc('tanggal_pesan')
            ->orderByDesc('id')
            ->get();

        $destinasiOptions = Destinasi::query()
            ->with('wilayah:id,nama_wilayah')
            ->where('status_aktif', true)
            ->orderBy('nama_destinasi')
            ->get();

        return Inertia::render('Tiket/Index', [
            'tiketList' => $tiketList,
            'destinasiOptions' => $destinasiOptions,
        ]);
    }

    public function pesanTiket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'tanggal_kunjungan' => ['required', 'date'],
            'jumlah_dewasa' => ['required', 'integer', 'min:1', 'max:50'],
            'jumlah_anak' => ['required', 'integer', 'min:0', 'max:50'],
            'metode_bayar' => ['required', 'in:QRIS,E-Wallet,Transfer Bank,Bayar di Loket'],
            'asal_pemesan' => ['nullable', 'in:Wisnus,Wisman'],
        ]);

        $user = $request->user();
        $dest = Destinasi::findOrFail($validated['destinasi_id']);

        if ($dest->harga_tiket_dewasa === null || $dest->harga_tiket_anak === null) {
            return back()->with('error', 'Tarif destinasi belum tersedia.');
        }

        $totalRp = ($dest->harga_tiket_dewasa * (int) $validated['jumlah_dewasa'])
            + ($dest->harga_tiket_anak * (int) $validated['jumlah_anak']);

        $tiket = DB::connection('warehouse')->transaction(function () use ($request, $validated, $user, $dest, $totalRp): Tiket {
            return DB::transaction(function () use ($request, $validated, $user, $dest, $totalRp): Tiket {
                $nextNum = Tiket::max('id') + 1;
                $kodeTiket = 'TKT-'.str_pad((string) $nextNum, 6, '0', STR_PAD_LEFT);

                $tiket = Tiket::create([
                    'kode_tiket' => $kodeTiket,
                    'destinasi_id' => $dest->id,
                    'user_id' => $user->id,
                    'kode_pengguna' => $user->kode_pengguna ?? ('U'.$user->id),
                    'nama_pemesan' => $user->name,
                    'email_pemesan' => $user->email,
                    'tanggal_pesan' => now()->toDateString(),
                    'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
                    'jumlah_dewasa' => (int) $validated['jumlah_dewasa'],
                    'jumlah_anak' => (int) $validated['jumlah_anak'],
                    'harga_dewasa' => $dest->harga_tiket_dewasa,
                    'harga_anak' => $dest->harga_tiket_anak,
                    'total_bayar_rp' => $totalRp,
                    'metode_bayar' => $validated['metode_bayar'],
                    'status' => 'Selesai',
                    'asal_pemesan' => $validated['asal_pemesan'] ?? ($user->bahasa === 'en' ? 'Wisman' : 'Wisnus'),
                    'sumber_data' => 'aplikasi_web',
                ]);

                $this->syncTicketToWarehouse($tiket, $dest);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'nama_user' => $user->name,
                    'peran' => $user->peran,
                    'aksi' => 'Pemesanan E-Tiket',
                    'modul' => 'Tiket',
                    'deskripsi' => "E-Tiket {$kodeTiket} diterbitkan untuk {$dest->nama_destinasi} senilai Rp ".number_format($totalRp, 0, ',', '.'),
                    'ip_address' => $request->ip(),
                ]);

                return $tiket;
            });
        });

        return redirect()->route('tiket.riwayat')->with('success', "Pembayaran via {$validated['metode_bayar']} berhasil! E-Tiket {$tiket->kode_tiket} telah diterbitkan lengkap dengan kode QR.");
    }

    private function syncTicketToWarehouse(Tiket $tiket, Destinasi $destinasi): void
    {
        DB::connection('warehouse')->table('fact_tiket')->insert([
            'tiket_id' => $tiket->id,
            'kode_tiket' => $tiket->kode_tiket,
            'destinasi_id' => $destinasi->id,
            'wilayah_id' => $destinasi->wilayah_id,
            'tanggal_pesan' => $tiket->tanggal_pesan->format('Y-m-d'),
            'tanggal_kunjungan' => $tiket->tanggal_kunjungan->format('Y-m-d'),
            'periode' => $tiket->tanggal_kunjungan->format('Y-m'),
            'jumlah_dewasa' => $tiket->jumlah_dewasa,
            'jumlah_anak' => $tiket->jumlah_anak,
            'harga_dewasa' => $tiket->harga_dewasa,
            'harga_anak' => $tiket->harga_anak,
            'total_bayar_rp' => $tiket->total_bayar_rp,
            'metode_bayar' => $tiket->metode_bayar,
            'status' => $tiket->status,
            'asal_pemesan' => $tiket->asal_pemesan,
            'user_id' => $tiket->kode_pengguna,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function batalkanTiket(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $tiket = Tiket::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if ($user->kode_pengguna) {
                $q->orWhere('kode_pengguna', $user->kode_pengguna);
            }
        })->findOrFail($id);

        if ($tiket->status === 'Sudah Check-In') {
            return back()->with('error', 'Tiket yang sudah digunakan check-in tidak dapat dibatalkan.');
        }

        $tiket->update(['status' => 'Dibatalkan']);
        DB::connection('warehouse')->table('fact_tiket')->where('tiket_id', $tiket->id)->update(['status' => 'Dibatalkan']);

        return back()->with('info', "Tiket {$tiket->kode_tiket} berhasil dibatalkan.");
    }

    public function storeUlasan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_pemandangan' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_kebersihan' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_akses' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_fasilitas' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_harga' => ['required', 'integer', 'min:1', 'max:5'],
            'teks_ulasan' => ['required', 'string', 'min:10', 'max:1500'],
        ]);

        $user = $request->user();
        $dest = Destinasi::findOrFail($validated['destinasi_id']);

        // Sentiment classification rule + aspect score calibration
        $avgAspect = ($validated['skor_pemandangan'] + $validated['skor_kebersihan'] + $validated['skor_akses'] + $validated['skor_fasilitas'] + $validated['skor_harga']) / 5.0;
        $combined = ($validated['rating'] * 0.6) + ($avgAspect * 0.4);
        $labelSentimen = $combined >= 3.7 ? 'positif' : ($combined >= 2.6 ? 'netral' : 'negatif');
        $skorSentimen = round(min(0.99, max(0.05, $combined / 5.0)), 4);

        // Privacy rule from PRD Section 11: abbreviated user name on reviews
        $parts = explode(' ', trim($user->name));
        $shortName = count($parts) > 1
            ? $parts[0].' '.mb_substr($parts[1], 0, 1).'.'
            : $parts[0];

        $ulasan = Ulasan::create([
            'destinasi_id' => $dest->id,
            'user_id' => $user->id,
            'kode_pengguna' => $user->kode_pengguna ?? ('U'.$user->id),
            'tanggal' => now()->toDateString(),
            'nama_pengguna' => $shortName,
            'bahasa' => $user->bahasa ?? 'id',
            'rating' => (int) $validated['rating'],
            'skor_pemandangan' => (int) $validated['skor_pemandangan'],
            'skor_kebersihan' => (int) $validated['skor_kebersihan'],
            'skor_akses' => (int) $validated['skor_akses'],
            'skor_fasilitas' => (int) $validated['skor_fasilitas'],
            'skor_harga' => (int) $validated['skor_harga'],
            'teks_ulasan' => $validated['teks_ulasan'],
            'label_sentimen' => $labelSentimen,
            'skor_sentimen' => $skorSentimen,
            'status_moderasi' => 'tayang',
            'sumber_data' => 'aplikasi_web',
        ]);

        // Sync to Data Warehouse fact_ulasan and update destination average rating
        $dw = DB::connection('warehouse');
        $dw->table('fact_ulasan')->insert([
            'ulasan_id' => $ulasan->id,
            'destinasi_id' => $dest->id,
            'wilayah_id' => $dest->wilayah_id,
            'kategori' => $dest->kategori,
            'tanggal' => $ulasan->tanggal->format('Y-m-d'),
            'periode' => $ulasan->tanggal->format('Y-m'),
            'nama_pengguna' => $shortName,
            'bahasa' => $ulasan->bahasa,
            'rating' => $ulasan->rating,
            'skor_pemandangan' => $ulasan->skor_pemandangan,
            'skor_kebersihan' => $ulasan->skor_kebersihan,
            'skor_akses' => $ulasan->skor_akses,
            'skor_fasilitas' => $ulasan->skor_fasilitas,
            'skor_harga' => $ulasan->skor_harga,
            'teks_ulasan' => $ulasan->teks_ulasan,
            'label_sentimen' => $labelSentimen,
            'user_id' => $ulasan->kode_pengguna,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newAvg = round((float) Ulasan::where('destinasi_id', $dest->id)->where('status_moderasi', '!=', 'disembunyikan')->avg('rating'), 2);
        $newCount = Ulasan::where('destinasi_id', $dest->id)->where('status_moderasi', '!=', 'disembunyikan')->count();
        $dest->update([
            'rating_rata_rata' => $newAvg,
            'jumlah_ulasan' => $newCount,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan dan penilaian 5 aspek Anda telah tercatat (Sentimen terdeteksi: '.ucfirst($labelSentimen).').');
    }
}
