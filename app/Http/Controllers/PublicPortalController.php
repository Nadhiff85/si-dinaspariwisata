<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Destinasi;
use App\Models\Event;
use App\Models\Pengaduan;
use App\Models\Ulasan;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicPortalController extends Controller
{
    public function beranda(Request $request): Response
    {
        $destinasiUnggulan = Destinasi::query()
            ->with('wilayah:id,nama_wilayah,jenis')
            ->where('status_aktif', true)
            ->orderByDesc('is_unggulan')
            ->orderByDesc('rating_rata_rata')
            ->limit(8)
            ->get();

        $eventTerdekat = Event::query()
            ->with(['wilayah:id,nama_wilayah', 'destinasi:id,nama_destinasi,slug'])
            ->where('status_persetujuan', 'disetujui')
            ->orderBy('tanggal_mulai')
            ->limit(4)
            ->get();

        $beritaTerbaru = Berita::query()
            ->with('wilayah:id,nama_wilayah')
            ->orderByDesc('tanggal_terbit')
            ->limit(3)
            ->get();

        $wilayahList = Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah', 'jenis']);
        $kategoriList = ['Bahari', 'Alam', 'Budaya & Sejarah', 'Religi', 'Ikon Kota'];

        $userWishlistIds = $request->user()
            ? $request->user()->wishlists()->pluck('destinasi_id')->all()
            : [];

        return Inertia::render('Beranda', [
            'destinasiUnggulan' => $destinasiUnggulan,
            'eventTerdekat' => $eventTerdekat,
            'beritaTerbaru' => $beritaTerbaru,
            'wilayahList' => $wilayahList,
            'kategoriList' => $kategoriList,
            'userWishlistIds' => $userWishlistIds,
        ]);
    }

    public function jelajah(Request $request): Response
    {
        $query = Destinasi::query()
            ->with('wilayah:id,nama_wilayah,jenis,kode_bps')
            ->where('status_aktif', true);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_destinasi', 'like', "%{$search}%")
                    ->orWhere('deskripsi_id', 'like', "%{$search}%")
                    ->orWhere('deskripsi_en', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori') && $request->input('kategori') !== 'SEMUA') {
            $query->where('kategori', (string) $request->input('kategori'));
        }

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', (int) $request->input('wilayah_id'));
        }

        if ($request->filled('rating_min')) {
            $query->where('rating_rata_rata', '>=', (float) $request->input('rating_min'));
        }

        if ($request->filled('harga_max')) {
            $query->where('harga_tiket_dewasa', '<=', (int) $request->input('harga_max'));
        }

        if ($request->filled('keramaian') && $request->input('keramaian') !== 'SEMUA') {
            $query->where('tingkat_keramaian_hari_ini', (string) $request->input('keramaian'));
        }

        $sort = (string) $request->input('urutkan', 'unggulan');
        match ($sort) {
            'rating_tertinggi' => $query->orderByDesc('rating_rata_rata')->orderByDesc('jumlah_ulasan'),
            'harga_terendah' => $query->orderBy('harga_tiket_dewasa'),
            'harga_tertinggi' => $query->orderByDesc('harga_tiket_dewasa'),
            'paling_sepi' => $query->orderBy('keramaian_persen_hari_ini'),
            'nama_az' => $query->orderBy('nama_destinasi'),
            default => $query->orderByDesc('is_unggulan')->orderByDesc('rating_rata_rata'),
        };

        $destinasiList = $query->get();

        // Attach 12-month visit counts & scores from warehouse
        $dw = DB::connection('warehouse');
        $skorMap = $dw->table('skor_destinasi')->get()->keyBy('destinasi_id');

        $destinasiEnriched = $destinasiList->map(function ($d) use ($skorMap) {
            $skor = $skorMap->get($d->id);
            $arr = $d->toArray();
            $arr['skor_unggulan'] = $skor ? (float) $skor->skor_total : null;
            $arr['kuadran'] = $skor ? $skor->kuadran : null;
            $arr['kunjungan_12bln'] = $skor ? (int) $skor->total_kunjungan_12bln : null;

            return $arr;
        });

        $wilayahList = Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah', 'jenis']);
        $kategoriList = ['Bahari', 'Alam', 'Budaya & Sejarah', 'Religi', 'Ikon Kota'];
        $userWishlistIds = $request->user()
            ? $request->user()->wishlists()->pluck('destinasi_id')->all()
            : [];

        return Inertia::render('Destinasi/Index', [
            'destinasiList' => $destinasiEnriched,
            'wilayahList' => $wilayahList,
            'kategoriList' => $kategoriList,
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'kategori' => (string) $request->input('kategori', 'SEMUA'),
                'wilayah_id' => $request->input('wilayah_id', ''),
                'rating_min' => $request->input('rating_min', ''),
                'harga_max' => $request->input('harga_max', ''),
                'keramaian' => (string) $request->input('keramaian', 'SEMUA'),
                'urutkan' => $sort,
            ],
            'userWishlistIds' => $userWishlistIds,
        ]);
    }

    public function detailDestinasi(Request $request, string $slug): Response
    {
        $destinasi = Destinasi::query()
            ->with('wilayah')
            ->where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int) $slug : 0)
            ->firstOrFail();

        $dw = DB::connection('warehouse');

        $ulasanList = Ulasan::query()
            ->where('destinasi_id', $destinasi->id)
            ->where('status_moderasi', '!=', 'disembunyikan')
            ->orderByDesc('tanggal')
            ->limit(25)
            ->get();

        // Aspect averages for Radar chart on detail page
        $aspekDestinasi = $dw->table('fact_ulasan')
            ->where('destinasi_id', $destinasi->id)
            ->selectRaw('
                ROUND(AVG(skor_pemandangan), 2) as pemandangan,
                ROUND(AVG(skor_kebersihan), 2) as kebersihan,
                ROUND(AVG(skor_akses), 2) as akses,
                ROUND(AVG(skor_fasilitas), 2) as fasilitas,
                ROUND(AVG(skor_harga), 2) as harga
            ')
            ->first();

        // Best days of week crowd average for this destination
        $laporanKeramaian = $dw->table('fact_kunjungan_harian')
            ->where('destinasi_id', $destinasi->id)
            ->orderByDesc('tanggal')
            ->first();

        $polaHari = $dw->table('fact_kunjungan_harian as fh')
            ->join('dim_waktu as dwk', 'fh.tanggal', '=', 'dwk.tanggal')
            ->where('fh.destinasi_id', $destinasi->id)
            ->select(
                'dwk.hari_dalam_minggu',
                'dwk.nama_hari',
                DB::raw('ROUND(AVG(fh.keramaian_persen), 1) as avg_keramaian'),
                DB::raw('ROUND(AVG(fh.total_pengunjung), 0) as avg_pengunjung')
            )
            ->groupBy('dwk.hari_dalam_minggu', 'dwk.nama_hari')
            ->orderBy('dwk.hari_dalam_minggu')
            ->get();

        // Monthly trend + 6-month forecast for this destination
        $trenBulanan = $dw->table('fact_kunjungan_bulanan')
            ->where('destinasi_id', $destinasi->id)
            ->whereBetween('periode', ['2025-11', '2026-10'])
            ->orderBy('periode')
            ->get();

        $prediksiDestinasi = $dw->table('hasil_prediksi')
            ->where('cakupan', 'destinasi')
            ->where('destinasi_id', $destinasi->id)
            ->whereNull('aktual_total')
            ->orderBy('periode')
            ->get();

        $skorInfo = $dw->table('skor_destinasi')->where('destinasi_id', $destinasi->id)->first();

        // Similar & nearby destinations (FR-19)
        $destinasiSerupa = Destinasi::query()
            ->with('wilayah:id,nama_wilayah')
            ->where('id', '!=', $destinasi->id)
            ->where(function ($q) use ($destinasi) {
                $q->where('kategori', $destinasi->kategori)
                    ->orWhere('wilayah_id', $destinasi->wilayah_id);
            })
            ->orderByDesc('rating_rata_rata')
            ->limit(4)
            ->get();

        $isWishlisted = $request->user()
            ? $request->user()->wishlists()->where('destinasi_id', $destinasi->id)->exists()
            : false;

        $userItineraries = $request->user()
            ? $request->user()->itineraries()->orderByDesc('created_at')->get(['id', 'judul_perjalanan', 'jumlah_hari'])
            : [];

        return Inertia::render('Destinasi/Show', [
            'destinasi' => $destinasi,
            'skorInfo' => $skorInfo,
            'aspekDestinasi' => $aspekDestinasi,
            'laporanKeramaian' => $laporanKeramaian,
            'polaHari' => $polaHari,
            'trenBulanan' => $trenBulanan,
            'prediksiDestinasi' => $prediksiDestinasi,
            'ulasanList' => $ulasanList,
            'destinasiSerupa' => $destinasiSerupa,
            'isWishlisted' => $isWishlisted,
            'userItineraries' => $userItineraries,
        ]);
    }

    public function petaWisata(): Response
    {
        $destinasiList = Destinasi::query()
            ->with('wilayah:id,nama_wilayah,jenis,kode_bps')
            ->where('status_aktif', true)
            ->orderBy('nama_destinasi')
            ->get();

        $wilayahList = Wilayah::orderBy('nama_wilayah')->get();
        $kategoriList = ['Bahari', 'Alam', 'Budaya & Sejarah', 'Religi', 'Ikon Kota'];

        return Inertia::render('Peta', [
            'destinasiList' => $destinasiList,
            'wilayahList' => $wilayahList,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function statistikPublik(Request $request): Response|RedirectResponse
    {
        if (! $request->user() || $request->user()->peran !== 'admin') {
            return redirect()->route('login')->with('info', 'Modul Analisis Statistik Resmi merupakan portal eksekutif Dinas Pariwisata. Silakan masuk menggunakan Akun Admin Dinas.');
        }

        return Inertia::render('Statistik');
    }

    public function informasiPublik(Request $request): Response
    {
        $events = Event::query()
            ->with(['wilayah:id,nama_wilayah', 'destinasi:id,nama_destinasi,slug'])
            ->where('status_persetujuan', 'disetujui')
            ->orderBy('tanggal_mulai')
            ->get();

        $beritaList = Berita::query()
            ->with('wilayah:id,nama_wilayah')
            ->orderByDesc('tanggal_terbit')
            ->get();

        $destinasiOptions = Destinasi::orderBy('nama_destinasi')->get(['id', 'nama_destinasi']);

        $pengaduanpublik = Pengaduan::query()
            ->with('destinasi:id,nama_destinasi')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Informasi', [
            'events' => $events,
            'beritaList' => $beritaList,
            'destinasiOptions' => $destinasiOptions,
            'pengaduanList' => $pengaduanpublik,
        ]);
    }

    public function storePengaduan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelapor' => ['required', 'string', 'max:120'],
            'email_pelapor' => ['required', 'email', 'max:150'],
            'destinasi_id' => ['nullable', 'exists:destinasi,id'],
            'kategori' => ['required', 'string', 'max:60'],
            'judul' => ['required', 'string', 'max:160'],
            'pesan' => ['required', 'string', 'max:2000'],
        ]);

        $count = Pengaduan::count() + 1;
        $kode = 'ADU-2026-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        Pengaduan::create([
            'kode_pengaduan' => $kode,
            'user_id' => $request->user()?->id,
            'destinasi_id' => $validated['destinasi_id'] ?: null,
            'nama_pelapor' => $validated['nama_pelapor'],
            'email_pelapor' => $validated['email_pelapor'],
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'pesan' => $validated['pesan'],
            'status' => 'baru',
        ]);

        return back()->with('success', "Laporan/Saran Anda berhasil dikirim dengan nomor tiket {$kode}. Status tindak lanjut dapat dipantau di halaman ini.");
    }

    /**
     * Rule-Based Recommendation Chatbot (FR-11, Section 7.5 PRD).
     */
    public function chatbotAsk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'locale' => ['nullable', 'string'],
        ]);

        $msg = mb_strtolower(trim($validated['message']));
        $isEn = ($validated['locale'] ?? session('locale', 'id')) === 'en';

        $query = Destinasi::query()->with('wilayah:id,nama_wilayah')->where('status_aktif', true);
        $matchedCategory = null;
        $matchedWilayah = null;
        $maxBudget = null;

        // 1. Detect Category Intent
        if (preg_match('/(pantai|laut|pulau|snorkeling|diving|selam|bahari|beach|island|sea|coral|togean)/i', $msg)) {
            $matchedCategory = 'Bahari';
            $query->where('kategori', 'Bahari');
        } elseif (preg_match('/(gunung|danau|air terjun|hutan|alam|nature|lake|forest|waterfall|tambing|lore lindu)/i', $msg)) {
            $matchedCategory = 'Alam';
            $query->where('kategori', 'Alam');
        } elseif (preg_match('/(budaya|sejarah|megalit|museum|adat|culture|history|megalith|bada)/i', $msg)) {
            $matchedCategory = 'Budaya & Sejarah';
            $query->where('kategori', 'Budaya & Sejarah');
        } elseif (preg_match('/(religi|masjid|gereja|ziarah|religious|mosque)/i', $msg)) {
            $matchedCategory = 'Religi';
            $query->where('kategori', 'Religi');
        } elseif (preg_match('/(kota|jembatan|ikon|taman kota|city|bridge)/i', $msg)) {
            $matchedCategory = 'Ikon Kota';
            $query->where('kategori', 'Ikon Kota');
        }

        // 2. Detect Regency/City Entity
        $wilayahAll = Wilayah::all();
        foreach ($wilayahAll as $w) {
            $shortName = mb_strtolower(str_replace(['Kabupaten ', 'Kota '], '', $w->nama_wilayah));
            if (str_contains($msg, $shortName)) {
                $matchedWilayah = $w->nama_wilayah;
                $query->where('wilayah_id', $w->id);
                break;
            }
        }

        // 3. Detect Budget / Price Constraint
        if (preg_match('/(gratis|\bfree\b)/i', $msg)) {
            $maxBudget = 0;
        } elseif (preg_match('/(\d+(?:[.,]\d+)?)\s*(ribu|rb|k)\b/i', $msg, $m)) {
            $maxBudget = (int) round(((float) str_replace(',', '.', $m[1])) * 1000);
        } elseif (preg_match('/(murah|hemat|cheap|budget)/i', $msg)) {
            $maxBudget = 15000;
        }
        if ($maxBudget !== null) {
            $query->where('harga_tiket_dewasa', '<=', $maxBudget);
        }

        // 4. Detect Low-Crowd Intent
        if (preg_match('/(sepi|tenang|quiet|calm|tidak ramai)/i', $msg)) {
            $query->orderBy('keramaian_persen_hari_ini');
        } else {
            $query->orderByDesc('rating_rata_rata');
        }

        $recommendations = $query->limit(3)->get();

        $filterDescId = [];
        $filterDescEn = [];
        if ($matchedCategory) {
            $filterDescId[] = "kategori {$matchedCategory}";
            $categoryEnglish = [
                'Bahari' => 'Marine',
                'Alam' => 'Nature',
                'Budaya & Sejarah' => 'Culture & History',
                'Religi' => 'Religious',
                'Ikon Kota' => 'City Landmarks',
            ];
            $filterDescEn[] = 'category '.$categoryEnglish[$matchedCategory];
        }
        if ($matchedWilayah) {
            $filterDescId[] = "wilayah {$matchedWilayah}";
            $filterDescEn[] = "region {$matchedWilayah}";
        }
        if ($maxBudget !== null) {
            $filterDescId[] = 'tiket ≤ Rp '.number_format($maxBudget, 0, ',', '.');
            $filterDescEn[] = 'ticket ≤ IDR '.number_format($maxBudget);
        }

        if ($recommendations->isEmpty()) {
            $replyText = $isEn
                ? 'No destinations match '.implode(', ', $filterDescEn).'. Try another region, category, or a higher admission budget.'
                : 'Belum ada destinasi yang sesuai dengan '.implode(', ', $filterDescId).'. Coba wilayah atau kategori lain, atau naikkan anggaran tiket masuk.';
        } elseif (! empty($filterDescId)) {
            $replyText = $isEn
                ? 'Here are the top recommended destinations in Central Sulawesi matching '.implode(', ', $filterDescEn).':'
                : 'Berikut rekomendasi destinasi terbaik di Sulawesi Tengah berdasarkan kriteria '.implode(', ', $filterDescId).':';
        } else {
            $replyText = $isEn
                ? 'Here are our highest-rated flagship destinations in Central Sulawesi. You can also ask by category (e.g. "Bahari", "Megalit"), region ("Palu", "Poso", "Togean"), or budget ("murah / < 20rb"):'
                : 'Berikut destinasi unggulan dengan rating tertinggi di Sulawesi Tengah. Anda juga dapat menanyakan kategori ("wisata bahari", "danau/alam", "sejarah"), wilayah ("Palu", "Poso", "Togean"), atau anggaran ("tiket di bawah 20 ribu"):';
        }

        return response()->json([
            'reply' => $replyText,
            'destinations' => $recommendations->map(fn ($d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'nama_destinasi' => $d->nama_destinasi,
                'wilayah' => $d->wilayah->nama_wilayah ?? '-',
                'kategori' => $d->kategori,
                'harga_dewasa' => $d->harga_tiket_dewasa,
                'jam_operasional' => $d->jam_operasional,
                'rating' => $d->rating_rata_rata,
                'keramaian' => $d->tingkat_keramaian_hari_ini,
                'foto_utama' => $d->foto_utama,
                'foto_attribution' => $d->foto_attribution,
            ]),
        ]);
    }
}
