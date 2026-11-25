<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Itinerari Perjalanan — {{ $itinerary->judul_perjalanan }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #12344D; font-size: 12px; line-height: 1.5; }
        .header { border-bottom: 2px solid #0E7C86; padding-bottom: 12px; margin-bottom: 18px; }
        .title { font-size: 20px; font-weight: bold; color: #0E7C86; margin: 0; }
        .subtitle { font-size: 12px; color: #556877; margin-top: 4px; }
        .meta-box { background: #F5EFE3; padding: 12px; border-radius: 6px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #12344D; color: #FFFFFF; padding: 8px; text-align: left; font-size: 11px; }
        td { border-bottom: 1px solid #E3DACB; padding: 8px; font-size: 11px; }
        .total-row { font-weight: bold; background: #D5EEF0; }
        .footer { margin-top: 24px; font-size: 10px; color: #556877; border-top: 1px solid #E3DACB; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">JELAJAH SULTENG — RENCANA PERJALANAN WISATA</p>
        <p class="subtitle">Portal Resmi Informasi & Analitik Pariwisata Provinsi Sulawesi Tengah</p>
    </div>

    <div class="meta-box">
        <strong>Judul Perjalanan:</strong> {{ $itinerary->judul_perjalanan }}<br>
        <strong>Periode Tanggal:</strong> {{ $itinerary->tanggal_mulai->format('d M Y') }} s/d {{ $itinerary->tanggal_selesai->format('d M Y') }} ({{ $itinerary->jumlah_hari }} Hari)<br>
        <strong>Peserta:</strong> {{ $itinerary->jumlah_dewasa }} Dewasa, {{ $itinerary->jumlah_anak }} Anak<br>
        <strong>Disusun Oleh:</strong> {{ $itinerary->user->name ?? 'Wisatawan' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Hari</th>
                <th>Jam</th>
                <th>Destinasi Wisata</th>
                <th>Kabupaten/Kota</th>
                <th>Kategori</th>
                <th>Catatan Aktivitas</th>
                <th style="text-align:right;">Estimasi Tiket (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itinerary->items as $item)
            <tr>
                <td>Hari ke-{{ $item->hari_ke }}</td>
                <td>{{ $item->jam_mulai }} ({{ $item->durasi_menit }} mnt)</td>
                <td><strong>{{ $item->destinasi->nama_destinasi ?? '-' }}</strong></td>
                <td>{{ $item->destinasi->wilayah->nama_wilayah ?? '-' }}</td>
                <td>{{ $item->destinasi->kategori ?? '-' }}</td>
                <td>{{ $item->catatan_item ?? '-' }}</td>
                <td style="text-align:right;">Rp {{ number_format($item->estimasi_biaya_rp, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align:right;">Total Estimasi Biaya Tiket Masuk:</td>
                <td style="text-align:right;">Rp {{ number_format($itinerary->estimasi_biaya_tiket_rp, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak dari sistem Jelajah Sulteng — Dinas Pariwisata Provinsi Sulawesi Tengah pada {{ now()->format('d M Y H:i') }}.
    </div>
</body>
</html>

