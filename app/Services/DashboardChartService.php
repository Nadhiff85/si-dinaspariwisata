<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardChartService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getDecisionCharts(array $filters = []): array
    {
        $period = $this->getReportingPeriod((string) ($filters['tahun'] ?? '12BLN'));
        $start = $period['start'].'-01';
        $end = CarbonImmutable::parse($period['end'].'-01')->endOfMonth()->toDateString();
        $warehouse = DB::connection('warehouse');
        $tickets = DB::connection('mysql')->table('tiket as t')
            ->join('destinasi as d', 'd.id', '=', 't.destinasi_id');
        $daily = $warehouse->table('fact_kunjungan_harian as f')
            ->join('dim_destinasi as d', 'd.destinasi_id', '=', 'f.destinasi_id')
            ->whereBetween('f.tanggal', [$start, $end]);
        foreach (['wilayah_id', 'kategori'] as $dimension) {
            if (! empty($filters[$dimension])) {
                $tickets->where('d.'.$dimension, $filters[$dimension]);
                $daily->where('d.'.$dimension, $filters[$dimension]);
            }
        }
        if (! empty($filters['destinasi_id'])) {
            $tickets->where('t.destinasi_id', (int) $filters['destinasi_id']);
            $daily->where('f.destinasi_id', (int) $filters['destinasi_id']);
        }
        $bookings = (clone $tickets)->whereBetween('t.tanggal_pesan', [$start, $end]);

        $hotels = $warehouse->table('fact_hotel_bulanan as h')
            ->join('dim_wilayah as w', 'w.wilayah_id', '=', 'h.wilayah_id')
            ->whereBetween('h.periode', [$period['start'], $period['end']])
            ->where('h.rata_lama_menginap_hari', '>', 0);
        if (! empty($filters['wilayah_id'])) {
            $hotels->where('h.wilayah_id', (int) $filters['wilayah_id']);
        }
        if (! empty($filters['destinasi_id'])) {
            $region = $warehouse->table('dim_destinasi')->where('destinasi_id', (int) $filters['destinasi_id'])->value('wilayah_id');
            $hotels->where('h.wilayah_id', $region ?? 0);
        }
        $stays = $hotels->select('h.wilayah_id', 'w.nama_wilayah')
            ->selectRaw('ROUND(AVG(h.rata_lama_menginap_hari), 2) as rata_hari, COUNT(*) as jumlah_laporan')
            ->groupBy('h.wilayah_id', 'w.nama_wilayah')->orderByDesc('rata_hari')->get();

        $leadTimes = (clone $bookings)->whereIn('t.status', ['Selesai', 'Sudah Check-In'])
            ->whereColumn('t.tanggal_kunjungan', '>=', 't.tanggal_pesan')
            ->selectRaw('DATEDIFF(t.tanggal_kunjungan, t.tanggal_pesan) as hari')->get();
        $leadBuckets = collect([
            ['rentang' => '0', 'min' => 0, 'max' => 0],
            ['rentang' => '1–3', 'min' => 1, 'max' => 3],
            ['rentang' => '4–7', 'min' => 4, 'max' => 7],
            ['rentang' => '8–14', 'min' => 8, 'max' => 14],
            ['rentang' => '15–30', 'min' => 15, 'max' => 30],
            ['rentang' => '>30', 'min' => 31, 'max' => PHP_INT_MAX],
        ])->map(fn (array $bucket): array => [
            'hari_sebelum_kunjungan' => $bucket['rentang'],
            'jumlah_pemesanan' => $leadTimes->filter(fn (object $row): bool => $row->hari >= $bucket['min'] && $row->hari <= $bucket['max'])->count(),
        ]);

        $cancellations = (clone $bookings)->select('t.destinasi_id', 'd.nama_destinasi')
            ->selectRaw("COUNT(*) as total_pemesanan, SUM(t.status = 'Dibatalkan') as dibatalkan, ROUND(100.0 * SUM(t.status = 'Dibatalkan') / COUNT(*), 2) as persen_batal")
            ->groupBy('t.destinasi_id', 'd.nama_destinasi')->orderByDesc('persen_batal')->get();

        $checkIns = (clone $bookings)->whereIn('t.status', ['Selesai', 'Sudah Check-In'])
            ->whereColumn('t.tanggal_kunjungan', '>=', 't.tanggal_pesan')
            ->where('t.tanggal_kunjungan', '<', now()->toDateString())
            ->select('t.destinasi_id', 'd.nama_destinasi')
            ->selectRaw("COUNT(*) as tiket_layak, SUM(t.waktu_checkin IS NOT NULL OR t.status = 'Sudah Check-In') as checkin_tercatat")
            ->groupBy('t.destinasi_id', 'd.nama_destinasi')->get()
            ->map(function (object $row): object {
                $row->belum_terverifikasi = (int) $row->tiket_layak - (int) $row->checkin_tercatat;
                $row->persen_terverifikasi = round(100 * (int) $row->checkin_tercatat / (int) $row->tiket_layak, 2);

                return $row;
            });

        $capacity = $daily->where('f.kapasitas_harian', '>', 0)
            ->select('f.destinasi_id', 'd.nama_destinasi')
            ->selectRaw('COUNT(*) as hari_tercatat, SUM(f.total_pengunjung > f.kapasitas_harian) as hari_melebihi, ROUND(100.0 * SUM(f.total_pengunjung > f.kapasitas_harian) / COUNT(*), 2) as persen_melebihi')
            ->groupBy('f.destinasi_id', 'd.nama_destinasi')->orderByDesc('hari_melebihi')->get();

        $history = (clone $tickets)->whereIn('t.status', ['Selesai', 'Sudah Check-In'])
            ->whereColumn('t.tanggal_kunjungan', '>=', 't.tanggal_pesan')
            ->where('t.tanggal_pesan', '<=', min($end, now()->toDateString()))
            ->select('t.user_id', 't.kode_pengguna', 't.tanggal_pesan')->orderBy('t.tanggal_pesan')->get();
        $buyers = [];
        foreach ($history as $ticket) {
            $identity = $ticket->user_id ? 'user:'.$ticket->user_id : (trim((string) $ticket->kode_pengguna) !== '' ? 'code:'.$ticket->kode_pengguna : null);
            if ($identity !== null) {
                $buyers[$identity][substr($ticket->tanggal_pesan, 0, 7)] = true;
            }
        }
        $cohorts = [];
        foreach ($buyers as $months) {
            $first = array_key_first($months);
            if ($first < $period['start'] || $first > $period['end']) {
                continue;
            }
            $cohorts[$first][] = $months;
        }
        ksort($cohorts);
        $retention = [];
        foreach ($cohorts as $cohort => $members) {
            $points = [];
            for ($offset = 1; $offset <= 5; $offset++) {
                $month = CarbonImmutable::parse($cohort.'-01')->addMonths($offset)->format('Y-m');
                $observed = $month <= min($period['end'], now()->subMonth()->format('Y-m'));
                $returning = $observed ? count(array_filter($members, fn (array $months): bool => isset($months[$month]))) : null;
                $points[] = ['bulan_ke' => $offset, 'jumlah_kembali' => $returning, 'persen' => $observed ? round(100 * $returning / count($members), 1) : null];
            }
            $retention[] = ['cohort' => $cohort, 'jumlah_pemesan' => count($members), 'retensi' => $points];
        }

        return [
            'chart_31_lama_menginap' => $stays,
            'chart_32_jarak_pemesanan' => $leadBuckets,
            'chart_33_pembatalan' => $cancellations,
            'chart_34_verifikasi_tiket' => $checkIns,
            'chart_35_kelebihan_kapasitas' => $capacity,
            'chart_36_pemesanan_ulang' => $retention,
            'period' => $period,
        ];
    }

    /**
     * @return array{start: string, end: string, previous_start: string, previous_end: string, comparable: bool}
     */
    public function getReportingPeriod(string $selection = '12BLN'): array
    {
        $monthlyVisits = DB::connection('warehouse')->table('fact_kunjungan_bulanan');
        $latest = $monthlyVisits->max('periode') ?: now()->format('Y-m');
        $end = CarbonImmutable::parse($latest.'-01');
        $start = $end->subMonths(11);

        if ($selection === 'ALL') {
            $start = CarbonImmutable::parse(($monthlyVisits->min('periode') ?: $latest).'-01');
        } elseif (preg_match('/^20\d{2}$/', $selection)) {
            $start = CarbonImmutable::parse($selection.'-01-01');
            if ($selection !== substr($latest, 0, 4)) {
                $end = $start->addMonths(11);
            }
        }

        return [
            'start' => $start->format('Y-m'),
            'end' => $end->format('Y-m'),
            'previous_start' => $start->subYear()->format('Y-m'),
            'previous_end' => $end->subYear()->format('Y-m'),
            'comparable' => $selection !== 'ALL',
        ];
    }

    /**
     * Get the 8 charts for Public / Tourist Statistics & Detail pages.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPublicCharts(array $filters = []): array
    {
        $dw = DB::connection('warehouse');
        $selectedDestIds = $filters['compare_ids'] ?? [1, 5, 11]; // Kepulauan Togean, Danau Poso, Pantai Talise
        $activeDestId = (int) ($filters['destinasi_id'] ?? 1);

        // Chart 1: Komposisi destinasi per kategori (Donut)
        $c1Rows = $dw->table('dim_destinasi')
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        // Chart 2: Sepuluh destinasi dengan rating tertinggi (Horizontal Bar)
        $c2Rows = $dw->table('fact_ulasan as fu')
            ->join('dim_destinasi as dd', 'fu.destinasi_id', '=', 'dd.destinasi_id')
            ->select(
                'dd.destinasi_id',
                'dd.nama_destinasi',
                'dd.kategori',
                'dd.nama_wilayah',
                DB::raw('ROUND(AVG(fu.rating), 2) as avg_rating'),
                DB::raw('COUNT(*) as total_ulasan')
            )
            ->groupBy('dd.destinasi_id', 'dd.nama_destinasi', 'dd.kategori', 'dd.nama_wilayah')
            ->orderByDesc('avg_rating')
            ->orderByDesc('total_ulasan')
            ->limit(10)
            ->get();

        // Chart 3: Tren kunjungan 12 bulan terakhir (Line)
        $c3Rows = $dw->table('fact_kunjungan_bulanan')
            ->whereBetween('periode', ['2025-11', '2026-10'])
            ->select(
                'periode',
                DB::raw('SUM(wisnus) as wisnus'),
                DB::raw('SUM(wisman) as wisman'),
                DB::raw('SUM(total_pengunjung) as total')
            )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // Chart 4: Keramaian per hari dalam seminggu dan per bulan (Heatmap)
        $c4Raw = $dw->table('fact_kunjungan_harian as fh')
            ->join('dim_waktu as dwk', 'fh.tanggal', '=', 'dwk.tanggal')
            ->whereBetween('dwk.periode', ['2025-11', '2026-10'])
            ->select(
                'dwk.hari_dalam_minggu',
                'dwk.nama_hari',
                'dwk.periode',
                DB::raw('ROUND(AVG(fh.keramaian_persen), 1) as avg_keramaian')
            )
            ->groupBy('dwk.hari_dalam_minggu', 'dwk.nama_hari', 'dwk.periode')
            ->orderBy('dwk.hari_dalam_minggu')
            ->orderBy('dwk.periode')
            ->get();

        $daysOrder = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $c4Series = [];
        foreach ($daysOrder as $dayNum => $dayName) {
            $dayRows = $c4Raw->where('hari_dalam_minggu', $dayNum);
            $dataPoints = [];
            foreach ($dayRows as $dr) {
                $dataPoints[] = [
                    'x' => $dr->periode,
                    'y' => (float) $dr->avg_keramaian,
                ];
            }
            $c4Series[] = [
                'name' => $dayName,
                'data' => $dataPoints,
            ];
        }

        // Chart 5: Jumlah destinasi & total kunjungan per kabupaten/kota (Choropleth Map)
        $c5Rows = $dw->table('dim_wilayah as dwl')
            ->leftJoin('dim_destinasi as dd', 'dwl.wilayah_id', '=', 'dd.wilayah_id')
            ->leftJoin('fact_kunjungan_bulanan as fkb', function ($join) {
                $join->on('dd.destinasi_id', '=', 'fkb.destinasi_id')
                    ->whereBetween('fkb.periode', ['2025-11', '2026-10']);
            })
            ->select(
                'dwl.wilayah_id',
                'dwl.kode_bps',
                'dwl.nama_wilayah',
                'dwl.jenis',
                'dwl.latitude',
                'dwl.longitude',
                DB::raw('COUNT(DISTINCT dd.destinasi_id) as jumlah_destinasi'),
                DB::raw('COALESCE(SUM(fkb.total_pengunjung), 0) as total_kunjungan_12bln')
            )
            ->groupBy('dwl.wilayah_id', 'dwl.kode_bps', 'dwl.nama_wilayah', 'dwl.jenis', 'dwl.latitude', 'dwl.longitude')
            ->orderByDesc('jumlah_destinasi')
            ->get();

        // Chart 6: Indikator keramaian hari ini per destinasi (Radial Gauge)
        $latestCrowd = $dw->table('fact_kunjungan_harian as fh')
            ->join('dim_destinasi as dd', 'fh.destinasi_id', '=', 'dd.destinasi_id')
            ->where('fh.destinasi_id', $activeDestId)
            ->orderByDesc('fh.tanggal')
            ->select('fh.*', 'dd.nama_destinasi', 'dd.kategori')
            ->first();

        // Chart 7: Perbandingan 5 aspek tiga destinasi pilihan (Radar)
        $c7Rows = $dw->table('fact_ulasan as fu')
            ->join('dim_destinasi as dd', 'fu.destinasi_id', '=', 'dd.destinasi_id')
            ->whereIn('fu.destinasi_id', $selectedDestIds)
            ->select(
                'dd.destinasi_id',
                'dd.nama_destinasi',
                DB::raw('ROUND(AVG(fu.skor_pemandangan), 2) as pemandangan'),
                DB::raw('ROUND(AVG(fu.skor_kebersihan), 2) as kebersihan'),
                DB::raw('ROUND(AVG(fu.skor_akses), 2) as akses'),
                DB::raw('ROUND(AVG(fu.skor_fasilitas), 2) as fasilitas'),
                DB::raw('ROUND(AVG(fu.skor_harga), 2) as harga')
            )
            ->groupBy('dd.destinasi_id', 'dd.nama_destinasi')
            ->get();

        // Chart 29 (Bonus Public Chart #8): Perbandingan Rata-rata Harga Tiket Dewasa & Anak per Kategori (Grouped Bar)
        $c29Rows = $dw->table('dim_destinasi')
            ->select(
                'kategori',
                DB::raw('ROUND(AVG(harga_tiket_dewasa), 0) as rata_dewasa'),
                DB::raw('ROUND(AVG(harga_tiket_anak), 0) as rata_anak'),
                DB::raw('MIN(harga_tiket_dewasa) as min_dewasa'),
                DB::raw('MAX(harga_tiket_dewasa) as max_dewasa')
            )
            ->groupBy('kategori')
            ->orderByDesc('rata_dewasa')
            ->get();

        return [
            'chart_01_kategori_donut' => $c1Rows,
            'chart_02_top_rating_bar' => $c2Rows,
            'chart_03_tren_12bln_line' => $c3Rows,
            'chart_04_keramaian_heatmap' => $c4Series,
            'chart_05_wilayah_choropleth' => $c5Rows,
            'chart_06_keramaian_gauge' => $latestCrowd,
            'chart_07_perbandingan_aspek_radar' => $c7Rows,
            'chart_29_harga_kategori_bar' => $c29Rows,
        ];
    }

    /**
     * Get the 6 charts for the Destination Manager (Pengelola Destinasi) Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getManagerCharts(int $destinasiId): array
    {
        $dw = DB::connection('warehouse');

        // Chart 8: Pengunjung harian 30 hari terakhir (Line)
        $c8Rows = $dw->table('fact_kunjungan_harian')
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('tanggal')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        $monthlyByPeriod = $dw->table('fact_kunjungan_bulanan')
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('periode')
            ->get()
            ->keyBy('periode');
        $c9Data = [];
        foreach ($monthlyByPeriod->take(6)->reverse() as $record) {
            $month = CarbonImmutable::parse($record->periode.'-01');
            $previousMonth = $monthlyByPeriod->get($month->subMonth()->format('Y-m'));
            $previousYear = $monthlyByPeriod->get($month->subYear()->format('Y-m'));
            $c9Data[] = [
                'bulan' => $record->periode,
                'bulan_ini' => (int) $record->total_pengunjung,
                'bulan_lalu' => $previousMonth === null ? null : (int) $previousMonth->total_pengunjung,
                'tahun_lalu' => $previousYear === null ? null : (int) $previousYear->total_pengunjung,
            ];
        }

        // Chart 10: Komposisi wisnus dan wisman (Pie)
        $c10Row = $dw->table('fact_kunjungan_bulanan')
            ->where('destinasi_id', $destinasiId)
            ->whereBetween('periode', ['2025-11', '2026-10'])
            ->selectRaw('COALESCE(SUM(wisnus), 0) as wisnus, COALESCE(SUM(wisman), 0) as wisman')
            ->first();

        // Chart 11: Penjualan tiket dewasa dan anak per minggu / periode (Stacked Bar)
        $c11Rows = $dw->table('fact_tiket')
            ->where('destinasi_id', $destinasiId)
            ->where('status', '!=', 'Dibatalkan')
            ->select(
                'periode',
                DB::raw('SUM(jumlah_dewasa) as total_dewasa'),
                DB::raw('SUM(jumlah_anak) as total_anak'),
                DB::raw('SUM(total_bayar_rp) as total_pendapatan')
            )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // Chart 12: Sentimen ulasan destinasi (Donut)
        $c12Rows = $dw->table('fact_ulasan')
            ->where('destinasi_id', $destinasiId)
            ->select('label_sentimen', DB::raw('COUNT(*) as total'))
            ->groupBy('label_sentimen')
            ->get();

        // Chart 30 (Bonus Manager Chart #6): Skor 5 Aspek Destinasi Saya vs Rata-rata Provinsi (Radar)
        $myAspects = $dw->table('fact_ulasan')
            ->where('destinasi_id', $destinasiId)
            ->selectRaw('
                ROUND(AVG(skor_pemandangan), 2) as pemandangan,
                ROUND(AVG(skor_kebersihan), 2) as kebersihan,
                ROUND(AVG(skor_akses), 2) as akses,
                ROUND(AVG(skor_fasilitas), 2) as fasilitas,
                ROUND(AVG(skor_harga), 2) as harga
            ')
            ->first();

        $provAspects = $dw->table('fact_ulasan')
            ->selectRaw('
                ROUND(AVG(skor_pemandangan), 2) as pemandangan,
                ROUND(AVG(skor_kebersihan), 2) as kebersihan,
                ROUND(AVG(skor_akses), 2) as akses,
                ROUND(AVG(skor_fasilitas), 2) as fasilitas,
                ROUND(AVG(skor_harga), 2) as harga
            ')
            ->first();

        return [
            'chart_08_harian_30hari_line' => $c8Rows,
            'chart_09_perbandingan_bulanan_bar' => $c9Data,
            'chart_10_komposisi_wisatawan_pie' => [
                'wisnus' => (int) ($c10Row->wisnus ?? 0),
                'wisman' => (int) ($c10Row->wisman ?? 0),
            ],
            'chart_11_tiket_dewasa_anak_stacked' => $c11Rows,
            'chart_12_sentimen_ulasan_donut' => $c12Rows,
            'chart_30_aspek_radar_pengelola' => [
                'destinasi' => $myAspects,
                'provinsi' => $provAspects,
            ],
        ];
    }

    /**
     * Get all 16 Executive Admin Charts (Charts #13 to #28) filtered by period, regency, and category.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getAdminCharts(array $filters = []): array
    {
        $dw = DB::connection('warehouse');
        $tahunFilter = $filters['tahun'] ?? '12BLN';
        $period = $this->getReportingPeriod($tahunFilter);
        $wilayahId = ! empty($filters['wilayah_id']) ? (int) $filters['wilayah_id'] : null;
        $kategori = ! empty($filters['kategori']) ? (string) $filters['kategori'] : null;
        $destinasiPredId = ! empty($filters['destinasi_id']) ? (int) $filters['destinasi_id'] : null;

        // Helper closure to apply territory & category filters on fact tables that have wilayah_id & kategori
        $applyFactFilters = function ($query, string $alias = '') use ($period, $wilayahId, $kategori, $destinasiPredId) {
            $pfx = $alias !== '' ? $alias.'.' : '';
            $query->whereBetween($pfx.'periode', [$period['start'], $period['end']]);
            if ($wilayahId) {
                $query->where($pfx.'wilayah_id', $wilayahId);
            }
            if ($kategori) {
                $query->where($pfx.'kategori', $kategori);
            }
            if ($destinasiPredId) {
                $query->where($pfx.'destinasi_id', $destinasiPredId);
            }

            return $query;
        };

        // Chart 13: Prediksi kunjungan enam bulan ke depan (Line dengan area interval)
        $c13Query = $dw->table('hasil_prediksi');
        if ($destinasiPredId) {
            $c13Query->where('cakupan', 'destinasi')->where('destinasi_id', $destinasiPredId);
        } else {
            $c13Query->where('cakupan', 'provinsi');
        }
        $c13Rows = $c13Query->orderBy('periode')->get();

        // Chart 14: Wisnus dan wisman per bulan 2022 sampai 2026 (Stacked Area)
        $c14Query = $dw->table('fact_kunjungan_bulanan');
        $applyFactFilters($c14Query);
        $c14Rows = $c14Query->select(
            'periode',
            DB::raw('SUM(wisnus) as wisnus'),
            DB::raw('SUM(wisman) as wisman'),
            DB::raw('SUM(total_pengunjung) as total'),
            DB::raw('SUM(pendapatan_tiket_rp) as pendapatan')
        )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // Chart 15: Total kunjungan per kabupaten dan kota (Peta Choropleth)
        $c15Rows = $dw->table('dim_wilayah as dwl')
            ->leftJoin('fact_kunjungan_bulanan as fkb', function ($join) use ($period, $kategori, $destinasiPredId) {
                $join->on('dwl.wilayah_id', '=', 'fkb.wilayah_id');
                $join->whereBetween('fkb.periode', [$period['start'], $period['end']]);
                if ($kategori) {
                    $join->where('fkb.kategori', $kategori);
                }
                if ($destinasiPredId) {
                    $join->where('fkb.destinasi_id', $destinasiPredId);
                }
            })
            ->when($wilayahId, fn ($query) => $query->where('dwl.wilayah_id', $wilayahId))
            ->select(
                'dwl.wilayah_id',
                'dwl.kode_bps',
                'dwl.nama_wilayah',
                'dwl.jenis',
                'dwl.latitude',
                'dwl.longitude',
                DB::raw('COALESCE(SUM(fkb.wisnus), 0) as wisnus'),
                DB::raw('COALESCE(SUM(fkb.wisman), 0) as wisman'),
                DB::raw('COALESCE(SUM(fkb.total_pengunjung), 0) as total_pengunjung'),
                DB::raw('COALESCE(SUM(fkb.pendapatan_tiket_rp), 0) as pendapatan_rp')
            )
            ->groupBy('dwl.wilayah_id', 'dwl.kode_bps', 'dwl.nama_wilayah', 'dwl.jenis', 'dwl.latitude', 'dwl.longitude')
            ->orderByDesc('total_pengunjung')
            ->get();

        // Chart 16: Kontribusi destinasi terhadap total kunjungan (Treemap)
        $c16Query = $dw->table('fact_kunjungan_bulanan as fkb')
            ->join('dim_destinasi as dd', 'fkb.destinasi_id', '=', 'dd.destinasi_id');
        $applyFactFilters($c16Query, 'fkb');
        $c16Rows = $c16Query->select(
            'dd.destinasi_id',
            'dd.nama_destinasi as x',
            'dd.kategori',
            'dd.nama_wilayah',
            DB::raw('SUM(fkb.total_pengunjung) as y'),
            DB::raw('SUM(fkb.pendapatan_tiket_rp) as pendapatan')
        )
            ->groupBy('dd.destinasi_id', 'dd.nama_destinasi', 'dd.kategori', 'dd.nama_wilayah')
            ->orderByDesc('y')
            ->get();

        // Chart 17: Sepuluh destinasi dengan skor unggulan tertinggi (Horizontal Bar)
        $c17Query = $dw->table('skor_destinasi as sd')
            ->join('dim_destinasi as dd', 'sd.destinasi_id', '=', 'dd.destinasi_id');
        if ($wilayahId) {
            $c17Query->where('dd.wilayah_id', $wilayahId);
        }
        if ($kategori) {
            $c17Query->where('dd.kategori', $kategori);
        }
        if ($destinasiPredId) {
            $c17Query->where('dd.destinasi_id', $destinasiPredId);
        }
        $c17Rows = $c17Query->select('sd.*', 'dd.nama_destinasi', 'dd.kategori', 'dd.nama_wilayah')
            ->orderByDesc('sd.skor_total')
            ->limit(10)
            ->get();

        // Chart 18: Kuadran kunjungan terhadap rating (Bubble: ukuran = pendapatan)
        $c18Query = $dw->table('skor_destinasi as sd')
            ->join('dim_destinasi as dd', 'sd.destinasi_id', '=', 'dd.destinasi_id');
        if ($wilayahId) {
            $c18Query->where('dd.wilayah_id', $wilayahId);
        }
        if ($kategori) {
            $c18Query->where('dd.kategori', $kategori);
        }
        if ($destinasiPredId) {
            $c18Query->where('dd.destinasi_id', $destinasiPredId);
        }
        $c18Rows = $c18Query->select('sd.*', 'dd.nama_destinasi', 'dd.kategori', 'dd.nama_wilayah')
            ->orderByDesc('sd.skor_total')
            ->get();

        // Chart 19: Pola musiman bulan dan tahun (Heatmap)
        $c19Query = $dw->table('fact_kunjungan_bulanan');
        $applyFactFilters($c19Query);
        $c19Raw = $c19Query->select(
            'tahun',
            'bulan',
            DB::raw('SUM(total_pengunjung) as total')
        )
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $bulanNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $c19Series = [];
        foreach ($c19Raw->pluck('tahun')->unique()->sort() as $yr) {
            $yrRows = $c19Raw->where('tahun', $yr)->keyBy('bulan');
            $pts = [];
            foreach ($bulanNames as $mNum => $mName) {
                if (! $yrRows->has($mNum)) {
                    continue;
                }
                $pts[] = [
                    'x' => $mName,
                    'y' => (int) ($yrRows->get($mNum)->total ?? 0),
                ];
            }
            $c19Series[] = [
                'name' => (string) $yr,
                'data' => $pts,
            ];
        }

        // Chart 20: TPK hotel dan kunjungan wisatawan (Dual-Axis: Line & Bar)
        $c20HotelQuery = $dw->table('fact_hotel_bulanan');
        if ($wilayahId) {
            $c20HotelQuery->where('wilayah_id', $wilayahId);
        }
        $c20Hotel = $c20HotelQuery->whereBetween('periode', [$period['start'], $period['end']])
            ->select(
                'periode',
                DB::raw('ROUND(AVG(tpk_persen), 2) as avg_tpk'),
                DB::raw('SUM(tamu_domestik + tamu_asing) as total_tamu')
            )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get()
            ->keyBy('periode');

        $c20VisitQuery = $dw->table('fact_kunjungan_bulanan');
        $applyFactFilters($c20VisitQuery);
        $c20Visits = $c20VisitQuery
            ->select('periode', DB::raw('SUM(total_pengunjung) as total_kunjungan'))
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        $c20Combo = [];
        foreach ($c20Visits as $vRow) {
            $hRow = $c20Hotel->get($vRow->periode);
            $c20Combo[] = [
                'periode' => $vRow->periode,
                'total_kunjungan' => (int) $vRow->total_kunjungan,
                'tpk_persen' => (float) ($hRow->avg_tpk ?? 40.0),
                'total_tamu_hotel' => (int) ($hRow->total_tamu ?? 0),
            ];
        }

        // Chart 21: Tamu domestik dan asing per kabupaten (Grouped Bar)
        $c21Query = $dw->table('fact_hotel_bulanan as fhb')
            ->join('dim_wilayah as dwl', 'fhb.wilayah_id', '=', 'dwl.wilayah_id');
        $c21Query->whereBetween('fhb.periode', [$period['start'], $period['end']]);
        if ($wilayahId) {
            $c21Query->where('fhb.wilayah_id', $wilayahId);
        }
        $c21Rows = $c21Query->select(
            'dwl.nama_wilayah',
            DB::raw('SUM(fhb.tamu_domestik) as tamu_domestik'),
            DB::raw('SUM(fhb.tamu_asing) as tamu_asing'),
            DB::raw('ROUND(AVG(fhb.tpk_persen), 1) as avg_tpk'),
            DB::raw('MAX(fhb.jumlah_kamar_bintang) as jumlah_kamar')
        )
            ->groupBy('dwl.wilayah_id', 'dwl.nama_wilayah')
            ->orderByDesc('tamu_domestik')
            ->get();

        // Chart 22: Asal wisatawan mancanegara (Donut)
        $c22Query = $dw->table('fact_asal_pengunjung')->where('jenis_wisatawan', 'Wisman');
        $c22Query->whereBetween('periode', [$period['start'], $period['end']]);
        $c22Rows = $c22Query->select('asal', DB::raw('SUM(jumlah) as total'))
            ->groupBy('asal')
            ->orderByDesc('total')
            ->get();

        // Chart 23: Asal wisatawan nusantara per provinsi (Horizontal Bar)
        $c23Query = $dw->table('fact_asal_pengunjung')->where('jenis_wisatawan', 'Wisnus');
        $c23Query->whereBetween('periode', [$period['start'], $period['end']]);
        $c23Rows = $c23Query->select('asal', DB::raw('SUM(jumlah) as total'))
            ->groupBy('asal')
            ->orderByDesc('total')
            ->get();

        // Chart 24: Sentimen ulasan per destinasi (100% Stacked Bar)
        $c24Query = $dw->table('hasil_sentimen as hs')
            ->join('dim_destinasi as dd', 'hs.destinasi_id', '=', 'dd.destinasi_id');
        if ($wilayahId) {
            $c24Query->where('dd.wilayah_id', $wilayahId);
        }
        if ($kategori) {
            $c24Query->where('dd.kategori', $kategori);
        }
        if ($destinasiPredId) {
            $c24Query->where('dd.destinasi_id', $destinasiPredId);
        }
        $c24Rows = $c24Query->select(
            'dd.nama_destinasi',
            'hs.positif_count',
            'hs.netral_count',
            'hs.negatif_count',
            'hs.persen_positif',
            'hs.persen_netral',
            'hs.persen_negatif',
            'hs.keluhan_kebersihan_persen'
        )
            ->orderByDesc('hs.persen_negatif')
            ->limit(12)
            ->get();

        // Chart 25: Tren rating rata-rata per bulan (Line)
        $c25Query = $dw->table('fact_ulasan');
        $applyFactFilters($c25Query);
        $c25Rows = $c25Query->select(
            'periode',
            DB::raw('ROUND(AVG(rating), 2) as avg_rating'),
            DB::raw('COUNT(*) as jumlah_ulasan')
        )
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // Chart 26: Profil aspek rata-rata per kategori (Radar)
        $c26Query = $dw->table('fact_ulasan');
        $applyFactFilters($c26Query);
        $c26Rows = $c26Query->select(
            'kategori',
            DB::raw('ROUND(AVG(skor_pemandangan), 2) as pemandangan'),
            DB::raw('ROUND(AVG(skor_kebersihan), 2) as kebersihan'),
            DB::raw('ROUND(AVG(skor_akses), 2) as akses'),
            DB::raw('ROUND(AVG(skor_fasilitas), 2) as fasilitas'),
            DB::raw('ROUND(AVG(skor_harga), 2) as harga')
        )
            ->groupBy('kategori')
            ->get();

        // Chart 27: Alur pengguna dari lihat destinasi sampai bayar tiket (Funnel)
        $c27Rows = $dw->table('fact_funnel_bulanan')
            ->whereBetween('periode', [$period['start'], $period['end']])
            ->select(
                'tahap_no',
                'tahap',
                DB::raw('SUM(jumlah_pengguna) as total_pengguna')
            )
            ->groupBy('tahap_no', 'tahap')
            ->orderBy('tahap_no')
            ->get();

        // Chart 28: Sebaran pengunjung harian per kategori (Box Plot: Min, Q1, Median, Q3, Max)
        $c28Raw = $dw->table('fact_kunjungan_harian')
            ->whereBetween('tanggal', [
                $period['start'].'-01',
                CarbonImmutable::parse($period['end'].'-01')->endOfMonth()->toDateString(),
            ])
            ->when($wilayahId, fn ($query) => $query->where('wilayah_id', $wilayahId))
            ->when($kategori, fn ($query) => $query->where('kategori', $kategori))
            ->when($destinasiPredId, fn ($query) => $query->where('destinasi_id', $destinasiPredId))
            ->select('kategori', 'total_pengunjung')
            ->orderBy('kategori')
            ->orderBy('total_pengunjung')
            ->get()
            ->groupBy('kategori');

        $c28BoxPlot = [];
        foreach ($c28Raw as $katName => $items) {
            $vals = $items->pluck('total_pengunjung')->map(fn ($v) => (int) $v)->values()->all();
            $cnt = count($vals);
            if ($cnt === 0) {
                continue;
            }
            $min = $vals[0];
            $q1 = $vals[(int) floor($cnt * 0.25)];
            $med = $vals[(int) floor($cnt * 0.50)];
            $q3 = $vals[(int) floor($cnt * 0.75)];
            $max = $vals[$cnt - 1];

            $c28BoxPlot[] = [
                'x' => $katName,
                'y' => [$min, $q1, $med, $q3, $max],
            ];
        }

        return [
            'chart_13_prediksi_6bln' => $c13Rows,
            'chart_14_wisnus_wisman_area' => $c14Rows,
            'chart_15_peta_kunjungan_wilayah' => $c15Rows,
            'chart_16_treemap_kontribusi' => $c16Rows,
            'chart_17_top_skor_unggulan' => $c17Rows,
            'chart_18_kuadran_bubble' => $c18Rows,
            'chart_19_musiman_heatmap' => $c19Series,
            'chart_20_tpk_kunjungan_combo' => $c20Combo,
            'chart_21_tamu_hotel_wilayah' => $c21Rows,
            'chart_22_asal_wisman_donut' => $c22Rows,
            'chart_23_asal_wisnus_bar' => $c23Rows,
            'chart_24_sentimen_destinasi_stacked' => $c24Rows,
            'chart_25_tren_rating_bulanan' => $c25Rows,
            'chart_26_aspek_kategori_radar' => $c26Rows,
            'chart_27_funnel_konversi' => $c27Rows,
            'chart_28_sebaran_kategori_boxplot' => $c28BoxPlot,
        ];
    }
}
