<script setup>
import { computed } from 'vue';
import KartuChart from '@/Components/chart/KartuChart.vue';
import ResponsiveChart from '@/Components/chart/ResponsiveChart.vue';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({ data: { type: Object, default: () => ({}) }, loading: Boolean });
const { t, locale } = useAppContext();
const number = (value, decimals = 0) => new Intl.NumberFormat(locale.value === 'en' ? 'en-US' : 'id-ID', { maximumFractionDigits: decimals }).format(value);
const definitions = computed(() => {
    const stays = props.data.chart_31_lama_menginap || [];
    const lead = props.data.chart_32_jarak_pemesanan || [];
    const cancellations = props.data.chart_33_pembatalan || [];
    const scans = props.data.chart_34_verifikasi_tiket || [];
    const scanPreview = [...scans].sort((a, b) => b.tiket_layak - a.tiket_layak).slice(0, 10);
    const capacity = props.data.chart_35_kelebihan_kapasitas || [];
    const cohorts = props.data.chart_36_pemesanan_ulang || [];
    const bar = (rows, label, value, seriesName, decimals = 0, max) => ({
        series: [{ name: seriesName, data: rows.map(row => Number(row[value])) }],
        options: { chart: { type: 'bar' }, colors: ['#0E7C86'], plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '65%' } }, dataLabels: { enabled: false }, xaxis: { categories: rows.map(row => row[label]), min: 0, ...(max ? { max } : {}), labels: { formatter: val => number(val, decimals) } }, tooltip: { y: { formatter: val => number(val, decimals) } } },
    });
    return [
        {
            id: 31, type: 'bar', title: t('Di wilayah mana wisatawan menginap paling lama?', 'Where do visitors stay the longest?'),
            description: t('Rata-rata lama menginap pada laporan hotel bulanan per wilayah.', 'Average length of stay across monthly regional hotel reports.'),
            insight: t('Data hotel mengikuti periode dan wilayah. Fokus destinasi memakai wilayahnya; kategori wisata tidak membatasi data hotel. Rerata antar laporan, bukan rerata tertimbang tamu.', 'Hotel data follows the period and region. A destination selects its region; tourism category does not filter hotels. Reports are averaged without guest weighting.'),
            source: 'fact_hotel_bulanan', rows: stays,
            columns: [{ key: 'nama_wilayah', label: t('Wilayah', 'Region') }, { key: 'rata_hari', label: t('Lama menginap (hari)', 'Length of stay (days)') }, { key: 'jumlah_laporan', label: t('Jumlah laporan', 'Report count') }],
            ...bar(stays, 'nama_wilayah', 'rata_hari', t('Hari menginap', 'Days stayed'), 2),
        },
        {
            id: 32, type: 'bar', title: t('Berapa hari sebelum kunjungan tiket dipesan?', 'How far ahead are tickets booked?'),
            description: t('Pemesanan berbayar dikelompokkan menurut jarak tanggal pesan dan tanggal kunjungan.', 'Paid bookings grouped by days between booking and visit dates.'),
            insight: t('Rentang 0 berarti pesan pada hari kunjungan. Pembatalan dan tanggal kunjungan sebelum pemesanan tidak disertakan.', 'Zero means same-day booking. Cancellations and visits dated before booking are excluded.'),
            source: 'tiket', rows: lead, columns: [{ key: 'hari_sebelum_kunjungan', label: t('Hari sebelum kunjungan', 'Days before visit') }, { key: 'jumlah_pemesanan', label: t('Pemesanan', 'Bookings') }],
            series: lead.some(row => row.jumlah_pemesanan > 0) ? [{ name: t('Pemesanan', 'Bookings'), data: lead.map(row => row.jumlah_pemesanan) }] : [],
            options: { colors: ['#0E7C86'], plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } }, dataLabels: { enabled: false }, xaxis: { categories: lead.map(row => row.hari_sebelum_kunjungan), title: { text: t('Hari sebelum kunjungan', 'Days before visit') } }, yaxis: { min: 0, labels: { formatter: val => number(val) } } },
        },
        {
            id: 33, type: 'bar', title: t('Destinasi mana memiliki tingkat pembatalan tertinggi?', 'Which destinations have the highest cancellation rates?'),
            description: t('10 destinasi teratas berdasarkan persentase pembatalan; semua destinasi tersedia di tabel.', 'Top 10 destinations by cancellation rate; all destinations are available in the table.'),
            insight: t('Persentase = pemesanan dibatalkan / seluruh pemesanan pada periode tanggal pesan. Periksa jumlah transaksi sebelum membandingkan destinasi.', 'Rate = cancelled bookings / all bookings in the booking-date period. Check transaction counts before comparing destinations.'),
            source: 'tiket', rows: cancellations,
            columns: [{ key: 'nama_destinasi', label: t('Destinasi', 'Destination') }, { key: 'total_pemesanan', label: t('Total pemesanan', 'Total bookings') }, { key: 'dibatalkan', label: t('Dibatalkan', 'Cancelled') }, { key: 'persen_batal', label: t('Pembatalan (%)', 'Cancellation (%)') }],
            ...bar(cancellations.slice(0, 10), 'nama_destinasi', 'persen_batal', t('Pembatalan (%)', 'Cancellation (%)'), 1, 100),
        },
        {
            id: 34, type: 'bar', title: t('Berapa tiket yang memiliki bukti check-in?', 'How many tickets have verified check-ins?'),
            description: t('10 destinasi dengan tiket berbayar terbanyak yang tanggal kunjungannya sudah lewat. Menghitung transaksi, bukan jumlah orang; semua destinasi tersedia di tabel.', 'Top 10 destinations by past paid bookings. Counts represent transactions, not people; all destinations are available in the table.'),
            insight: t('Belum terverifikasi tidak berarti tidak datang. Data historis belum mencatat waktu check-in; tiket masa depan dan pembatalan tidak dihitung.', 'Unverified does not mean absent. Historical data lacks check-in timestamps; future visits and cancellations are excluded.'),
            source: 'tiket.waktu_checkin / status', rows: scans,
            columns: [{ key: 'nama_destinasi', label: t('Destinasi', 'Destination') }, { key: 'tiket_layak', label: t('Tiket berbayar yang sudah lewat', 'Past paid bookings') }, { key: 'checkin_tercatat', label: t('Check-in tercatat', 'Verified check-ins') }, { key: 'belum_terverifikasi', label: t('Belum terverifikasi', 'Unverified') }, { key: 'persen_terverifikasi', label: t('Terverifikasi (%)', 'Verified (%)') }],
            series: [{ name: t('Check-in tercatat', 'Verified check-ins'), data: scanPreview.map(row => Number(row.checkin_tercatat)) }, { name: t('Belum terverifikasi', 'Unverified'), data: scanPreview.map(row => row.belum_terverifikasi) }],
            options: { chart: { stacked: true }, colors: ['#0E7C86', '#D9CFB8'], plotOptions: { bar: { horizontal: true, barHeight: '65%' } }, dataLabels: { enabled: false }, xaxis: { categories: scanPreview.map(row => row.nama_destinasi), labels: { formatter: val => number(val) } } },
        },
        {
            id: 35, type: 'bar', title: t('Destinasi mana paling sering melebihi kapasitas?', 'Which destinations most often exceed capacity?'),
            description: t('10 destinasi dengan hari kelebihan kapasitas terbanyak pada periode terpilih.', 'Top 10 destinations by days exceeding capacity in the selected period.'),
            insight: t('Dihitung saat jumlah pengunjung lebih besar dari kapasitas harian. Kapasitas nol dikecualikan; jumlah hari tercatat tersedia di tabel.', 'A day counts when visitors exceed daily capacity. Zero capacity is excluded; recorded-day counts are available in the table.'),
            source: 'fact_kunjungan_harian', rows: capacity,
            columns: [{ key: 'nama_destinasi', label: t('Destinasi', 'Destination') }, { key: 'hari_tercatat', label: t('Hari tercatat', 'Recorded days') }, { key: 'hari_melebihi', label: t('Hari melebihi kapasitas', 'Days over capacity') }, { key: 'persen_melebihi', label: t('Hari melebihi (%)', 'Days over (%)') }],
            ...bar(capacity.slice(0, 10), 'nama_destinasi', 'hari_melebihi', t('Hari melebihi kapasitas', 'Days over capacity')),
        },
        {
            id: 36, type: 'heatmap', title: t('Apakah pemesan kembali membeli tiket?', 'Do bookers return to buy tickets again?'),
            description: t('Cohort berdasarkan bulan pemesanan berbayar pertama, diikuti selama lima bulan berikutnya.', 'Cohorts based on the first paid-booking month, followed for the next five months.'),
            insight: t('Mengukur pemesanan ulang, bukan bukti kunjungan ulang. Penyebut tetap jumlah pemesan unik dalam cohort. Tanda — berarti bulan pengamatan belum lengkap.', 'Measures repeat bookings, not verified repeat visits. The denominator is the cohort’s unique bookers. A dash means the observation month is incomplete.'),
            source: 'tiket / identitas pemesan', rows: cohorts.flatMap(row => row.retensi.map(point => ({ cohort: row.cohort, pemesan_awal: row.jumlah_pemesan, bulan_ke: point.bulan_ke, pemesan_kembali: point.jumlah_kembali ?? '—', persen: point.persen ?? '—' }))),
            columns: [{ key: 'cohort', label: t('Cohort', 'Cohort') }, { key: 'pemesan_awal', label: t('Pemesan awal', 'Initial bookers') }, { key: 'bulan_ke', label: t('Bulan ke-', 'Month offset') }, { key: 'pemesan_kembali', label: t('Pemesan kembali', 'Returning bookers') }, { key: 'persen', label: t('Pemesanan ulang (%)', 'Repeat booking (%)') }],
            series: cohorts.map(row => ({ name: `${row.cohort} (${row.jumlah_pemesan})`, data: row.retensi.map(point => ({ x: `M+${point.bulan_ke}`, y: point.persen ?? -1 })) })),
            options: { colors: ['#0E7C86'], dataLabels: { enabled: true, formatter: val => val < 0 ? '—' : `${number(val, 1)}%`, style: { fontSize: '10px' } }, plotOptions: { heatmap: { colorScale: { min: 0, max: 100, ranges: [
                { from: -1, to: -0.1, color: '#E3DACB', foreColor: '#556877', name: t('Belum lengkap', 'Incomplete') },
                { from: 0, to: 19.9, color: '#E6F4F5', foreColor: '#12344D', name: '0–19%' },
                { from: 20, to: 39.9, color: '#B4DEE1', foreColor: '#12344D', name: '20–39%' },
                { from: 40, to: 59.9, color: '#6BC5B8', foreColor: '#12344D', name: '40–59%' },
                { from: 60, to: 79.9, color: '#1F9AA5', foreColor: '#FFFFFF', name: '60–79%' },
                { from: 80, to: 100, color: '#0A5F67', foreColor: '#FFFFFF', name: '80–100%' },
            ] } } }, tooltip: { y: { formatter: val => val < 0 ? t('Bulan pengamatan belum lengkap', 'Observation month incomplete') : `${number(val, 1)}%` } } },
        },
    ];
});
</script>

<template>
    <div class="decision-chart-grid grid grid-cols-1 gap-6 xl:grid-cols-2">
        <KartuChart v-for="chart in definitions" :key="chart.id" :nomor="chart.id" :judul="chart.title" :subjudul="chart.description" :jenis="chart.type === 'heatmap' ? 'Cohort heatmap' : 'Bar chart'" :insight="chart.insight" :sumber="chart.source" :rows="chart.rows" :columns="chart.columns" :loading="loading">
            <ResponsiveChart :type="chart.type" :height="chart.id === 36 ? 400 : 320" :options="chart.options" :series="chart.series" />
        </KartuChart>
    </div>
</template>
