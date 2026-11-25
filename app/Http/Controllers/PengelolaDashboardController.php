<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Destinasi;
use App\Models\Event;
use App\Models\ImportLog;
use App\Models\KunjunganHarian;
use App\Models\PerubahanDestinasi;
use App\Models\Tiket;
use App\Models\Ulasan;
use App\Services\DashboardChartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PengelolaDashboardController extends Controller
{
    public function __construct(public DashboardChartService $chartService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $destinasiId = (int) ($request->input('destinasi_id') ?: ($user->destinasi_id ?: 1));

        // Ensure Pengelola only accesses their own destination unless user is Admin previewing
        if ($user->peran === 'pengelola_destinasi' && $user->destinasi_id) {
            $destinasiId = (int) $user->destinasi_id;
        }

        $destinasi = Destinasi::with('wilayah')->findOrFail($destinasiId);
        $charts = $this->chartService->getManagerCharts($destinasiId);

        $kunjunganTerbaru = KunjunganHarian::query()
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('tanggal')
            ->limit(15)
            ->get();

        $tiketTerbaru = Tiket::query()
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('tanggal_kunjungan')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $ulasanDestinasi = Ulasan::query()
            ->where('destinasi_id', $destinasiId)
            ->where('status_moderasi', '!=', 'disembunyikan')
            ->orderByDesc('tanggal')
            ->limit(20)
            ->get();

        $riwayatPerubahan = PerubahanDestinasi::query()
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('created_at')
            ->get();

        $dw = DB::connection('warehouse');
        $kunjunganHariIni = $kunjunganTerbaru->first();
        $laporanBulananTerbaru = $dw->table('fact_kunjungan_bulanan')
            ->where('destinasi_id', $destinasiId)
            ->orderByDesc('periode')
            ->first(['periode', 'total_pengunjung', 'pendapatan_tiket_rp']);

        $allDestinations = $user->peran === 'admin'
            ? Destinasi::orderBy('nama_destinasi')->get(['id', 'nama_destinasi'])
            : Destinasi::where('id', $destinasiId)->get(['id', 'nama_destinasi']);

        return Inertia::render('Pengelola/Dashboard', [
            'destinasi' => $destinasi,
            'allDestinations' => $allDestinations,
            'kpi' => [
                'tanggal_laporan' => $kunjunganHariIni?->tanggal?->format('Y-m-d'),
                'periode_bulanan' => $laporanBulananTerbaru?->periode,
                'hari_ini_total' => (int) ($kunjunganHariIni->total_pengunjung ?? 0),
                'hari_ini_keramaian_persen' => (float) ($kunjunganHariIni->keramaian_persen ?? 0),
                'hari_ini_status' => $kunjunganHariIni->tingkat_keramaian ?? 'Sedang',
                'bulan_ini_total' => (int) ($laporanBulananTerbaru?->total_pengunjung ?? 0),
                'bulan_ini_pendapatan_rp' => (int) ($laporanBulananTerbaru?->pendapatan_tiket_rp ?? 0),
                'rating_rata_rata' => $destinasi->rating_rata_rata,
                'jumlah_ulasan' => $destinasi->jumlah_ulasan,
            ],
            'charts' => $charts,
            'kunjunganTerbaru' => $kunjunganTerbaru,
            'tiketTerbaru' => $tiketTerbaru,
            'ulasanDestinasi' => $ulasanDestinasi,
            'riwayatPerubahan' => $riwayatPerubahan,
        ]);
    }

    public function storeKunjunganHarian(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'tanggal' => ['required', 'date'],
            'wisnus' => ['required', 'integer', 'min:0', 'max:50000'],
            'wisman' => ['required', 'integer', 'min:0', 'max:50000'],
            'catatan_operasional' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $destId = ($user->peran === 'pengelola_destinasi' && $user->destinasi_id)
            ? (int) $user->destinasi_id
            : (int) $validated['destinasi_id'];

        $dest = Destinasi::findOrFail($destId);
        $total = (int) $validated['wisnus'] + (int) $validated['wisman'];
        $kapasitas = max(1, (int) $dest->kapasitas_harian);
        $persen = round(($total / $kapasitas) * 100, 1);
        $tingkat = $persen > 75.0 ? 'Ramai' : ($persen >= 40.0 ? 'Sedang' : 'Sepi');

        $record = KunjunganHarian::updateOrCreate(
            ['destinasi_id' => $dest->id, 'tanggal' => $validated['tanggal']],
            [
                'wisnus' => (int) $validated['wisnus'],
                'wisman' => (int) $validated['wisman'],
                'total_pengunjung' => $total,
                'kapasitas_harian' => $kapasitas,
                'keramaian_persen' => $persen,
                'tingkat_keramaian' => $tingkat,
                'diinput_oleh' => $user->id,
                'catatan_operasional' => $validated['catatan_operasional'] ?? 'Input langsung Pengelola',
                'sumber_data' => 'input_pengelola',
            ]
        );

        // Sync to Data Warehouse fact_kunjungan_harian & update destination live crowd badge
        DB::connection('warehouse')->table('fact_kunjungan_harian')->updateOrInsert(
            ['harian_id' => $record->id],
            [
                'tanggal' => $validated['tanggal'],
                'destinasi_id' => $dest->id,
                'wilayah_id' => $dest->wilayah_id,
                'kategori' => $dest->kategori,
                'wisnus' => $record->wisnus,
                'wisman' => $record->wisman,
                'total_pengunjung' => $record->total_pengunjung,
                'kapasitas_harian' => $record->kapasitas_harian,
                'keramaian_persen' => $record->keramaian_persen,
                'tingkat_keramaian' => $record->tingkat_keramaian,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $dest->update([
            'tingkat_keramaian_hari_ini' => $tingkat,
            'keramaian_persen_hari_ini' => $persen,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'nama_user' => $user->name,
            'peran' => $user->peran,
            'aksi' => 'Input Kunjungan Harian',
            'modul' => 'Pengelola Destinasi',
            'deskripsi' => "Mencatat {$total} pengunjung ({$tingkat} - {$persen}%) di {$dest->nama_destinasi} untuk tanggal {$validated['tanggal']}.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Data kunjungan tanggal {$validated['tanggal']} ({$total} pengunjung — {$tingkat}) berhasil disimpan dan disinkronkan ke dashboard!");
    }

    public function importKunjunganCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'file_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $user = $request->user();
        $destId = ($user->peran === 'pengelola_destinasi' && $user->destinasi_id)
            ? (int) $user->destinasi_id
            : (int) $request->input('destinasi_id');

        $dest = Destinasi::findOrFail($destId);
        $file = $request->file('file_csv');
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($lines as $idx => $line) {
            if ($idx === 0 && str_contains(strtolower($line), 'tanggal')) {
                continue;
            }
            $cols = str_getcsv($line);
            if (count($cols) < 3) {
                $gagal++;
                $errors[] = 'Baris '.($idx + 1).': Format kolom kurang dari 3 (tanggal,wisnus,wisman)';

                continue;
            }
            $tgl = trim($cols[0]);
            $wisnus = (int) trim($cols[1]);
            $wisman = (int) trim($cols[2]);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
                $gagal++;
                $errors[] = 'Baris '.($idx + 1).": Format tanggal '{$tgl}' tidak valid (gunakan YYYY-MM-DD)";

                continue;
            }

            $tot = $wisnus + $wisman;
            $cap = max(1, $dest->kapasitas_harian);
            $pct = round(($tot / $cap) * 100, 1);
            $lvl = $pct > 75.0 ? 'Ramai' : ($pct >= 40.0 ? 'Sedang' : 'Sepi');

            KunjunganHarian::updateOrCreate(
                ['destinasi_id' => $dest->id, 'tanggal' => $tgl],
                [
                    'wisnus' => $wisnus,
                    'wisman' => $wisman,
                    'total_pengunjung' => $tot,
                    'kapasitas_harian' => $cap,
                    'keramaian_persen' => $pct,
                    'tingkat_keramaian' => $lvl,
                    'diinput_oleh' => $user->id,
                    'sumber_data' => 'impor_csv',
                ]
            );
            $berhasil++;
        }

        ImportLog::create([
            'nama_file' => $file->getClientOriginalName(),
            'jenis_data' => 'Impor CSV Kunjungan Harian Pengelola',
            'total_baris' => $berhasil + $gagal,
            'baris_berhasil' => $berhasil,
            'baris_gagal' => $gagal,
            'detail_gagal_json' => $errors,
            'status' => $gagal === 0 ? 'berhasil' : 'sebagian_gagal',
            'diimpor_oleh' => $user->id,
        ]);

        return back()->with('success', "Impor CSV selesai: {$berhasil} baris berhasil disimpan, {$gagal} baris gagal.");
    }

    public function verifikasiTiket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_tiket' => ['required', 'string', 'max:50'],
        ]);

        $kode = strtoupper(trim($validated['kode_tiket']));
        $tiket = Tiket::with('destinasi')->where('kode_tiket', $kode)->first();

        if (! $tiket) {
            return back()->with('error', "Kode E-Tiket '{$kode}' tidak ditemukan di dalam sistem.");
        }

        $user = $request->user();
        if ($user->peran === 'pengelola_destinasi' && $user->destinasi_id && $tiket->destinasi_id !== (int) $user->destinasi_id) {
            return back()->with('error', "Tiket {$kode} adalah untuk destinasi '{$tiket->destinasi->nama_destinasi}', bukan destinasi yang Anda kelola.");
        }

        if ($tiket->status === 'Dibatalkan') {
            return back()->with('error', "Tiket {$kode} berstatus DIBATALKAN dan tidak dapat digunakan check-in.");
        }

        if ($tiket->status === 'Sudah Check-In') {
            return back()->with('info', "Tiket {$kode} sudah pernah diverifikasi check-in sebelumnya.");
        }

        $tiket->update([
            'status' => 'Sudah Check-In',
            'waktu_checkin' => now(),
            'diverifikasi_oleh' => $user->id,
        ]);

        DB::connection('warehouse')->table('fact_tiket')->where('tiket_id', $tiket->id)->update(['status' => 'Sudah Check-In']);

        return back()->with('success', "Check-in Berhasil! Tiket {$kode} ({$tiket->jumlah_dewasa} Dewasa, {$tiket->jumlah_anak} Anak — Rp ".number_format($tiket->total_bayar_rp, 0, ',', '.').') telah terverifikasi.');
    }

    public function balasUlasan(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'balasan_pengelola' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $ulasan = Ulasan::findOrFail($id);

        if ($user->peran === 'pengelola_destinasi' && $user->destinasi_id && $ulasan->destinasi_id !== (int) $user->destinasi_id) {
            abort(403);
        }

        $ulasan->update([
            'balasan_pengelola' => $validated['balasan_pengelola'],
            'dibalas_pada' => now(),
            'dibalas_oleh' => $user->id,
        ]);

        return back()->with('success', "Balasan resmi untuk ulasan {$ulasan->nama_pengguna} berhasil dipublikasikan.");
    }

    public function ajukanPerubahanProfil(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'harga_tiket_dewasa' => ['required', 'integer', 'min:0'],
            'harga_tiket_anak' => ['required', 'integer', 'min:0'],
            'jam_operasional' => ['required', 'string', 'max:60'],
            'kapasitas_harian' => ['required', 'integer', 'min:10'],
            'deskripsi_id' => ['required', 'string'],
            'deskripsi_en' => ['required', 'string'],
            'alasan_perubahan' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $dest = Destinasi::findOrFail($validated['destinasi_id']);

        // Update non-sensitive description directly, queue price/capacity change for Admin approval (FR-22, FR-33)
        $dest->update([
            'deskripsi_id' => $validated['deskripsi_id'],
            'deskripsi_en' => $validated['deskripsi_en'],
        ]);

        PerubahanDestinasi::create([
            'destinasi_id' => $dest->id,
            'pengelola_id' => $user->id,
            'jenis_perubahan' => 'Harga Tiket, Jam Operasional & Kapasitas',
            'data_lama_json' => [
                'harga_tiket_dewasa' => $dest->harga_tiket_dewasa,
                'harga_tiket_anak' => $dest->harga_tiket_anak,
                'jam_operasional' => $dest->jam_operasional,
                'kapasitas_harian' => $dest->kapasitas_harian,
            ],
            'data_baru_json' => [
                'harga_tiket_dewasa' => (int) $validated['harga_tiket_dewasa'],
                'harga_tiket_anak' => (int) $validated['harga_tiket_anak'],
                'jam_operasional' => $validated['jam_operasional'],
                'kapasitas_harian' => (int) $validated['kapasitas_harian'],
            ],
            'alasan_perubahan' => $validated['alasan_perubahan'],
            'status' => 'menunggu',
        ]);

        return back()->with('success', 'Deskripsi destinasi diperbarui, dan pengajuan perubahan harga tiket/jam operasional telah dikirim ke antrean persetujuan Admin Dinas.');
    }

    public function ajukanEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinasi_id' => ['required', 'exists:destinasi,id'],
            'nama_event_id' => ['required', 'string', 'max:160'],
            'nama_event_en' => ['nullable', 'string', 'max:160'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi_detail' => ['required', 'string', 'max:160'],
            'kategori_event' => ['required', 'string', 'max:60'],
            'harga_tiket_rp' => ['required', 'integer', 'min:0'],
            'deskripsi_id' => ['required', 'string'],
        ]);

        $dest = Destinasi::findOrFail($validated['destinasi_id']);

        Event::create([
            'slug' => Str::slug($validated['nama_event_id']).'-'.rand(100, 999),
            'nama_event_id' => $validated['nama_event_id'],
            'nama_event_en' => $validated['nama_event_en'] ?: $validated['nama_event_id'],
            'wilayah_id' => $dest->wilayah_id,
            'destinasi_id' => $dest->id,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'lokasi_detail' => $validated['lokasi_detail'],
            'kategori_event' => $validated['kategori_event'],
            'deskripsi_id' => $validated['deskripsi_id'],
            'deskripsi_en' => $validated['deskripsi_id'],
            'harga_tiket_rp' => (int) $validated['harga_tiket_rp'],
            'status_persetujuan' => 'menunggu',
            'dibuat_oleh' => $request->user()->id,
        ]);

        return back()->with('success', 'Usulan event destinasi berhasil dikirim dan menunggu persetujuan Admin Dinas untuk tampil di Kalender Event.');
    }
}
