<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Eksekutif Pariwisata Sulawesi Tengah</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #12344D; font-size: 11px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0E7C86; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; color: #12344D; margin: 0; }
        .subtitle { font-size: 11px; color: #0E7C86; margin-top: 3px; }
        h3 { color: #0E7C86; font-size: 13px; margin-top: 18px; margin-bottom: 6px; border-left: 4px solid #F26B4F; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th { background: #12344D; color: #FFFFFF; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { border-bottom: 1px solid #E3DACB; padding: 6px 8px; font-size: 10px; }
        .num { text-align: right; }
        .note { background: #F5EFE3; padding: 8px 10px; border-radius: 4px; font-size: 10px; color: #556877; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">LAPORAN EKSEKUTIF ANALITIK & KEPUTUSAN PARIWISATA SULAWESI TENGAH</p>
        <p class="subtitle">Dinas Pariwisata Provinsi Sulawesi Tengah | Dicetak oleh: {{ $dicetakOleh }} ({{ $tanggalCetak }})</p>
    </div>

    <h3>1. Prediksi Kunjungan Wisatawan Provinsi (6 Bulan ke Depan: Nov 2026 – Apr 2027)</h3>
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Model</th>
                <th class="num">Prediksi Wisnus</th>
                <th class="num">Prediksi Wisman</th>
                <th class="num">Total Prediksi</th>
                <th class="num">Interval Bawah–Atas</th>
                <th class="num">MAPE (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prediksiProvinsi as $p)
            <tr>
                <td><strong>{{ $p->periode }}</strong></td>
                <td>{{ $p->model_terpilih }}</td>
                <td class="num">{{ number_format($p->prediksi_wisnus, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($p->prediksi_wisman, 0, ',', '.') }}</td>
                <td class="num"><strong>{{ number_format($p->prediksi_total, 0, ',', '.') }}</strong></td>
                <td class="num">{{ number_format($p->batas_bawah, 0, ',', '.') }} – {{ number_format($p->batas_atas, 0, ',', '.') }}</td>
                <td class="num">{{ $p->mape_persen }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>2. Peringkat Skor & Pemetaan Kuadran Destinasi Unggulan</h3>
    <table>
        <thead>
            <tr>
                <th>Destinasi</th>
                <th>Wilayah</th>
                <th>Kategori</th>
                <th class="num">Kunjungan 12 Bln</th>
                <th class="num">Rating</th>
                <th class="num">Skor Total</th>
                <th>Kuadran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topDestinasi->take(12) as $d)
            <tr>
                <td><strong>{{ $d->nama_destinasi }}</strong></td>
                <td>{{ $d->nama_wilayah }}</td>
                <td>{{ $d->kategori }}</td>
                <td class="num">{{ number_format($d->total_kunjungan_12bln, 0, ',', '.') }}</td>
                <td class="num">{{ $d->rating_rata_rata }}</td>
                <td class="num"><strong>{{ $d->skor_total }}</strong></td>
                <td>{{ $d->kuadran }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>3. Rekomendasi Aksi Prioritas & Status Tindak Lanjut</h3>
    <table>
        <thead>
            <tr>
                <th>Aturan</th>
                <th>Prioritas</th>
                <th>Rekomendasi & Kondisi Terdeteksi</th>
                <th>Saran Tindakan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekomendasi as $r)
            <tr>
                <td>{{ $r->kode_aturan }}</td>
                <td><strong>{{ strtoupper($r->prioritas) }}</strong></td>
                <td><strong>{{ $r->judul_id }}</strong><br>{{ $r->kondisi_terdeteksi_id }}</td>
                <td>{{ $r->saran_tindakan_id }}</td>
                <td>{{ strtoupper($r->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note">
        <strong>Catatan Transparansi Data:</strong> Indikator perhotelan dan kunjungan dikalibrasi langsung dengan publikasi resmi BPS Provinsi Sulawesi Tengah (TPK Hotel Bintang 46,52% dan 1.782 kamar hotel bintang) serta Buku Rekap Data Pariwisata Resmi Dinas Pariwisata Provinsi Sulawesi Tengah 2022–2025.
    </div>
</body>
</html>

