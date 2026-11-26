<script setup>
import ResponsiveChart from '@/Components/chart/ResponsiveChart.vue';
import { computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KartuChart from '@/Components/chart/KartuChart.vue';
import KartuDestinasi from '@/Components/destinasi/KartuDestinasi.vue';
import PhotoCredit from '@/Components/destinasi/PhotoCredit.vue';
import { useAppContext, getLocalDate } from '@/composables/useAppContext';
import {
    Heart, MapPin, Star, Clock, Users, Ticket, MessageSquare,
    CheckCircle2, Navigation, Calendar, Sparkles
} from 'lucide-vue-next';

const props = defineProps({
    destinasi: { type: Object, required: true },
    skorInfo: { type: Object, default: null },
    aspekDestinasi: { type: Object, default: () => ({}) },
    laporanKeramaian: { type: Object, default: null },
    polaHari: { type: Array, default: () => [] },
    trenBulanan: { type: Array, default: () => [] },
    prediksiDestinasi: { type: Array, default: () => [] },
    ulasanList: { type: Array, default: () => [] },
    destinasiSerupa: { type: Array, default: () => [] },
    isWishlisted: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { t, locale, formatCurrency, formatNumber, formatDate } = useAppContext();

// Form Pemesanan E-Tiket
const ticketForm = useForm({
    destinasi_id: props.destinasi.id,
    tanggal_kunjungan: getLocalDate(),
    jumlah_dewasa: 2,
    jumlah_anak: 0,
    metode_bayar: 'QRIS',
    asal_pemesan: 'Wisnus',
});

const totalHargaTiket = computed(() => {
    return (Number(props.destinasi.harga_tiket_dewasa) * Number(ticketForm.jumlah_dewasa)) +
           (Number(props.destinasi.harga_tiket_anak) * Number(ticketForm.jumlah_anak));
});

const submitTiket = () => {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    ticketForm.post('/tiket/pesan');
};

// Form Rating & Ulasan 5 Aspek
const reviewForm = useForm({
    destinasi_id: props.destinasi.id,
    rating: 5,
    skor_pemandangan: 5,
    skor_kebersihan: 4,
    skor_akses: 4,
    skor_fasilitas: 4,
    skor_harga: 5,
    teks_ulasan: '',
});

const submitUlasan = () => {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    reviewForm.post('/ulasan', {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset('teks_ulasan'),
    });
};

const toggleWishlist = () => {
    router.post('/wishlist/toggle', { destinasi_id: props.destinasi.id }, { preserveScroll: true });
};

// Chart #6: Radial Gauge Keramaian Hari Ini
const gaugeSeries = computed(() => [Math.round(Number(props.laporanKeramaian?.keramaian_persen ?? 0))]);
const gaugeOptions = computed(() => ({
    chart: { type: 'radialBar', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    colors: [
        props.laporanKeramaian?.tingkat_keramaian === 'Ramai'
            ? '#F26B4F'
            : props.laporanKeramaian?.tingkat_keramaian === 'Sepi'
              ? '#2E8B57'
              : '#E0A422',
    ],
    plotOptions: {
        radialBar: {
            hollow: { size: '64%' },
            dataLabels: {
                name: { show: true, fontSize: '12px', fontWeight: 700, offsetY: -6 },
                value: {
                    show: true,
                    fontSize: '26px',
                    fontWeight: 800,
                    offsetY: 8,
                    formatter: (val) => `${val}%`,
                },
            },
        },
    },
    labels: [props.laporanKeramaian?.tingkat_keramaian || t('Belum ada laporan', 'No report yet')],
}));

// Radar 5 Aspek Destinasi
const hasAspekData = computed(() => {
    if (!props.aspekDestinasi) return false;
    const vals = [
        props.aspekDestinasi.pemandangan,
        props.aspekDestinasi.kebersihan,
        props.aspekDestinasi.akses,
        props.aspekDestinasi.fasilitas,
        props.aspekDestinasi.harga,
    ];
    return vals.some((v) => v !== null && v !== undefined && Number(v) > 0);
});

const radarSeries = computed(() => [
    {
        name: props.destinasi.nama_destinasi,
        data: [
            (props.aspekDestinasi?.pemandangan == null ? null : Number(props.aspekDestinasi?.pemandangan)),
            (props.aspekDestinasi?.kebersihan == null ? null : Number(props.aspekDestinasi?.kebersihan)),
            (props.aspekDestinasi?.akses == null ? null : Number(props.aspekDestinasi?.akses)),
            (props.aspekDestinasi?.fasilitas == null ? null : Number(props.aspekDestinasi?.fasilitas)),
            (props.aspekDestinasi?.harga == null ? null : Number(props.aspekDestinasi?.harga)),
        ],
    },
]);
const radarOptions = computed(() => ({
    chart: { type: 'radar', toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    colors: ['#0E7C86'],
    dataLabels: { enabled: false },
    xaxis: { categories: ['Pemandangan', 'Kebersihan', 'Akses Jalan', 'Fasilitas', 'Kesesuaian Harga'] },
    yaxis: { show: false, min: 0, max: 5, tickAmount: 5 },
    markers: { size: 4 },
}));

// Bar Keramaian per Hari dalam Seminggu
const hasPolaHariData = computed(() => {
    return Array.isArray(props.polaHari) && props.polaHari.length > 0 && props.polaHari.some((d) => Number(d.avg_keramaian) > 0);
});

const dayBarSeries = computed(() => [
    {
        name: 'Rata-rata Keramaian (%)',
        data: props.polaHari.map((d) => Number(d.avg_keramaian)),
    },
]);
const dayBarOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    colors: ['#0E7C86'],
    plotOptions: { bar: { borderRadius: 6, columnWidth: '52%' } },
    dataLabels: {
        enabled: true,
        formatter: (v) => `${Math.round(v)}%`,
        style: { fontSize: '11px', fontFamily: 'JetBrains Mono, monospace', fontWeight: 700, colors: ['#FFFFFF'] },
        dropShadow: { enabled: false },
    },
    xaxis: { categories: props.polaHari.map((d) => d.nama_hari) },
    yaxis: { max: 100, labels: { formatter: (v) => `${Math.round(v)}%` } },
}));
</script>

<template>
    <AppLayout>
        <Head :title="destinasi.nama_destinasi" />

        <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
            <nav class="page-breadcrumb" :aria-label="t('Jejak navigasi', 'Breadcrumb')"><Link href="/" class="hover:text-utama-teks">{{ t('Beranda', 'Home') }}</Link><span aria-hidden="true">/</span><Link href="/destinasi" class="hover:text-utama-teks">{{ t('Destinasi', 'Destinations') }}</Link><span aria-hidden="true">/</span><span aria-current="page">{{ destinasi.nama_destinasi }}</span></nav>
            <!-- Galeri Foto Atas (1 Besar + Info Ringkas sesuai Wireframe DESIGN.md) -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-stretch">
                <div class="flex flex-col lg:col-span-8">
                    <div class="relative flex-1 overflow-hidden rounded-3xl min-h-[340px] sm:min-h-[420px]">
                        <img
                            :src="destinasi.foto_utama"
                            :alt="destinasi.nama_destinasi"
                            :style="{ objectPosition: destinasi.foto_attribution?.position || 'center' }"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#12344D]/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 flex flex-wrap items-end justify-between gap-4 text-white">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-[#0E7C86] px-3 py-1 text-xs font-bold">
                                        {{ destinasi.kategori }}
                                    </span>
                                    <span v-if="skorInfo" class="rounded-lg bg-white/20 backdrop-blur-xs px-3 py-1 text-xs font-bold"> {{ t("Kuadran:", "Quadrant:") }} {{ skorInfo.kuadran }} {{ t("(Skor", "(Score") }} {{ skorInfo.skor_total }})
                                    </span>
                                </div>
                                <h1 class="font-heading mt-2 text-2xl font-extrabold sm:text-4xl">
                                    {{ destinasi.nama_destinasi }}
                                </h1>
                                <div class="mt-1 flex flex-wrap items-center gap-4 text-xs text-[#D5EEF0]">
                                    <span class="inline-flex items-center gap-1">
                                        <MapPin class="h-3.5 w-3.5" /> {{ destinasi.wilayah?.nama_wilayah }}
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <Clock class="h-3.5 w-3.5" /> {{ t("Jam Buka:", "Opening hours:") }} {{ destinasi.jam_operasional || t('Belum tersedia', 'Unavailable') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-bold text-[#E0A422]">
                                        <Star class="h-3.5 w-3.5 fill-[#E0A422]" /> {{ destinasi.rating_rata_rata }} ({{ destinasi.jumlah_ulasan }} {{ t("ulasan)", "reviews)") }} </span>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="toggleWishlist"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-[#12344D] shadow-lg transition hover:bg-[#D5EEF0]"
                            >
                                <Heart class="h-4 w-4" :class="isWishlisted ? 'fill-[#C2492E] text-[#C2492E]' : ''" />
                                <span>{{ isWishlisted ? t('Tersimpan di Wishlist', 'Saved to wishlist') : t('Simpan ke Wishlist', 'Save to wishlist') }}</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="destinasi.foto_attribution" class="mt-2.5 space-y-0.5 px-1 text-tinta-redup">
                        <p class="text-xs">{{ t(destinasi.foto_attribution.caption_id, destinasi.foto_attribution.caption_en) }}</p>
                        <PhotoCredit :photo="destinasi.foto_attribution" />
                    </div>
                </div>

                <!-- Chart #6: Radial Gauge Indikator Keramaian Hari Ini -->
                <div class="lg:col-span-4">
                    <KartuChart
                        public-view
                        nomor="6"
                        :judul="t('Keramaian pada laporan terakhir', 'Latest reported crowd level')"
                        :subjudul="laporanKeramaian?.tanggal ? `${t('Tanggal laporan:', 'Report date:')} ${formatDate(laporanKeramaian.tanggal)}` : t('Belum ada laporan', 'No report yet')"
                        jenis="Radial Gauge"
                        :insight="t('Persentase pengunjung terhadap kapasitas harian pada tanggal laporan.', 'Recorded visitors as a percentage of daily capacity on the reporting date.')"
                        sumber="fact_kunjungan_harian"
                    >
                        <ResponsiveChart v-if="laporanKeramaian" type="radialBar" height="250" :options="gaugeOptions" :series="gaugeSeries" />
                        <p v-else class="py-12 text-center text-sm text-tinta-redup">{{ t('Belum ada data keramaian.', 'No crowd report is available.') }}</p>
                    </KartuChart>
                </div>
            </div>

            <!-- Konten Utama (Kiri: Deskripsi, Chart & Ulasan | Kanan: Kartu Pemesanan E-Tiket) -->
            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
                <div class="space-y-8 lg:col-span-8">
                    <!-- Deskripsi & Fasilitas -->
                    <div class="kartu-togean p-6">
                        <h2 class="font-heading text-xl font-bold text-[var(--warna-teks)]"> {{ t("Tentang", "About") }} {{ destinasi.nama_destinasi }}
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-[var(--warna-teks)]">
                            {{ locale === 'en' ? (destinasi.deskripsi_en || destinasi.deskripsi_id) : destinasi.deskripsi_id }}
                        </p>

                        <div class="mt-5 grid grid-cols-2 gap-3 border-t border-[var(--warna-garis)] pt-4 sm:grid-cols-4 text-xs">
                            <div class="rounded-xl bg-[var(--warna-pasir)] p-3">
                                <span class="text-[var(--warna-teks-redup)] block">{{ t("Tiket Dewasa", "Adult ticket") }}</span>
                                <strong class="text-sm font-bold text-[#0A5F67] tabular-nums">{{ formatCurrency(destinasi.harga_tiket_dewasa) }}</strong>
                            </div>
                            <div class="rounded-xl bg-[var(--warna-pasir)] p-3">
                                <span class="text-[var(--warna-teks-redup)] block">{{ t("Tiket Anak", "Child ticket") }}</span>
                                <strong class="text-sm font-bold text-[#0A5F67] tabular-nums">{{ formatCurrency(destinasi.harga_tiket_anak) }}</strong>
                            </div>
                            <div class="rounded-xl bg-[var(--warna-pasir)] p-3">
                                <span class="text-[var(--warna-teks-redup)] block">{{ t("Koordinat GPS", "GPS coordinates") }}</span>
                                <strong class="text-sm font-bold tabular-nums">{{ destinasi.latitude }}, {{ destinasi.longitude }}</strong>
                            </div>
                            <div class="rounded-xl bg-[var(--warna-pasir)] p-3">
                                <span class="text-[var(--warna-teks-redup)] block">{{ t("Prediksi Bulan Depan", "Next-month forecast") }}</span>
                                <strong class="text-sm font-bold text-[#0E7C86] tabular-nums">
                                    {{ prediksiDestinasi[0]?.prediksi_total == null ? t('Belum tersedia', 'Unavailable') : `${formatNumber(prediksiDestinasi[0].prediksi_total)} ${t('orang', 'visitors')}` }} </strong>
                            </div>
                        </div>
                    </div>

                    <!-- 2 Chart Analitik Detail Destinasi (Radar 5 Aspek & Pola Hari Terbaik) -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <KartuChart
                            public-view
                            :judul="t('Bagaimana penilaian 5 aspek di destinasi ini?', 'Explore visitor experience ratings')"
                            :subjudul="t('Rata-rata skor 1 sampai 5 dari ulasan pengunjung', 'Average visitor review scores on a five-point scale')"
                            jenis="Radar Chart"
                            :insight="hasAspekData ? t('Menunjukkan kekuatan utama destinasi pada keindahan pemandangan dan kesesuaian harga tiket.', 'Compare scenery, cleanliness, access, facilities, and value.') : t('Belum ada ulasan terverifikasi untuk membentuk grafik radar 5 aspek.', 'No verified reviews yet to plot 5-aspect radar chart.')"
                            sumber="fact_ulasan"
                        >
                            <ResponsiveChart v-if="hasAspekData" type="radar" height="260" :options="radarOptions" :series="radarSeries" />
                            <div v-else class="flex flex-col items-center justify-center h-[260px] text-center p-6 text-tinta-redup">
                                <Star class="h-10 w-10 text-utama/40 mb-2" />
                                <p class="font-bold text-sm text-tinta">{{ t("Belum Ada Ulasan 5 Aspek", "No 5-Aspect Reviews Yet") }}</p>
                                <p class="text-xs mt-1 max-w-xs">{{ t("Jadilah pengunjung pertama yang memberikan penilaian 5 aspek melalui formulir di bawah ini.", "Be the first visitor to provide ratings using the review form below.") }}</p>
                            </div>
                        </KartuChart>

                        <KartuChart
                            public-view
                            :judul="t('Hari apa yang paling nyaman untuk berkunjung?', 'Which day is best to visit?')"
                            :subjudul="t('Rata-rata tingkat keramaian per hari dalam seminggu', 'Average crowd percentage by day of week')"
                            jenis="Bar Chart"
                            :insight="hasPolaHariData ? t('Hari kerja (Senin–Kamis) jauh lebih lengang dibanding lonjakan akhir pekan (Sabtu–Minggu).', 'Weekdays are noticeably quieter than weekend peaks.') : t('Pola kunjungan harian sedang dikumpulkan oleh petugas gerbang destinasi.', 'Daily visit patterns are being compiled by destination gate officers.')"
                            sumber="fact_kunjungan_harian"
                            :table-headers="['Kode Hari', 'Nama Hari', 'Keramaian (%)', 'Rata-rata Pengunjung']"
                            :table-rows="polaHari"
                        >
                            <ResponsiveChart v-if="hasPolaHariData" type="bar" height="260" :options="dayBarOptions" :series="dayBarSeries" />
                            <div v-else class="flex flex-col items-center justify-center h-[260px] text-center p-6 text-tinta-redup">
                                <Calendar class="h-10 w-10 text-utama/40 mb-2" />
                                <p class="font-bold text-sm text-tinta">{{ t("Pola Harian Sedang Dihimpun", "Daily Pattern Being Compiled") }}</p>
                                <p class="text-xs mt-1 max-w-xs">{{ t("Data pola kunjungan harian akan tampil otomatis seiring pencatatan buku tamu & e-tiket berkala.", "Daily visitor pattern will display automatically as guest book & e-ticket records accumulate.") }}</p>
                            </div>
                        </KartuChart>
                    </div>

                    <!-- Formulir & Daftar Ulasan 5 Aspek -->
                    <div class="kartu-togean p-6">
                        <h2 class="font-heading text-xl font-bold text-[var(--warna-teks)] flex items-center gap-2">
                            <MessageSquare class="h-5 w-5 text-[#0E7C86]" />
                            <span>{{ t("Ulasan & Penilaian 5 Aspek (", "Visitor reviews (") }}{{ ulasanList.length }})</span>
                        </h2>

                        <!-- Form Tulis Ulasan -->
                        <form @submit.prevent="submitUlasan" class="mt-4 rounded-2xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] p-4">
                        <div v-if="Object.keys(reviewForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in reviewForm.errors" :key="field">{{ message }}</li></ul></div>
                            <h3 class="font-heading text-sm font-bold text-[var(--warna-teks)]"> {{ t("Bagikan Pengalaman Kunjungan Anda", "Share your experience") }} </h3>
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-6 text-xs">
                                <div>
                                    <label for="field-destinasi-show-reviewForm-rating" class="mb-1 block font-bold">{{ t("Rating Utama", "Overall rating") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.rating)" id="field-destinasi-show-reviewForm-rating" v-model="reviewForm.rating" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2 font-bold">
                                        <option :value="5">5 ★ Sangat Puas</option>
                                        <option :value="4">4 ★ Puas</option>
                                        <option :value="3">3 ★ Cukup</option>
                                        <option :value="2">2 ★ Kurang</option>
                                        <option :value="1">1 ★ Buruk</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-destinasi-show-reviewForm-skor_pemandangan" class="mb-1 block font-semibold">{{ t("Pemandangan", "Scenery") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.skor_pemandangan)" id="field-destinasi-show-reviewForm-skor_pemandangan" v-model="reviewForm.skor_pemandangan" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} / 5</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-destinasi-show-reviewForm-skor_kebersihan" class="mb-1 block font-semibold">{{ t("Kebersihan", "Cleanliness") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.skor_kebersihan)" id="field-destinasi-show-reviewForm-skor_kebersihan" v-model="reviewForm.skor_kebersihan" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} / 5</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-destinasi-show-reviewForm-skor_akses" class="mb-1 block font-semibold">{{ t("Akses", "Access") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.skor_akses)" id="field-destinasi-show-reviewForm-skor_akses" v-model="reviewForm.skor_akses" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} / 5</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-destinasi-show-reviewForm-skor_fasilitas" class="mb-1 block font-semibold">{{ t("Fasilitas", "Facilities") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.skor_fasilitas)" id="field-destinasi-show-reviewForm-skor_fasilitas" v-model="reviewForm.skor_fasilitas" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} / 5</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-destinasi-show-reviewForm-skor_harga" class="mb-1 block font-semibold">{{ t("Harga", "Value") }}</label>
                                    <select :aria-invalid="Boolean(reviewForm.errors.skor_harga)" id="field-destinasi-show-reviewForm-skor_harga" v-model="reviewForm.skor_harga" class="h-9 w-full rounded-lg border border-[var(--warna-garis)] bg-white px-2">
                                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} / 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <textarea :aria-invalid="Boolean(reviewForm.errors.teks_ulasan)"
                                    v-model="reviewForm.teks_ulasan"
                                    rows="3"
                                    required
                                    :placeholder="t('Ceritakan pengalaman Anda mengenai keindahan alam, kebersihan, akses jalan, dan fasilitas di destinasi ini...', 'Share your experience of the scenery, cleanliness, access, and facilities?')"
                                    class="w-full rounded-xl border border-[var(--warna-garis)] bg-white p-3 text-xs text-[#12344D]"
                                ></textarea>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <button type="submit" :disabled="reviewForm.processing" class="tombol-utama px-5 text-xs"> {{ t("Kirim Ulasan & Analisis Sentimen Otomatis", "Submit review") }} </button>
                            </div>
                        </form>

                        <!-- Daftar Ulasan -->
                        <div class="mt-6 space-y-4">
                            <article
                                v-for="ul in ulasanList"
                                :key="ul.id"
                                class="rounded-xl border border-[var(--warna-garis)] p-4 text-xs"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-[var(--warna-teks)]">{{ ul.nama_pengguna }}</span>
                                        <span class="rounded-md bg-[#E0A422]/20 px-2 py-0.5 font-bold text-[#8A6108]">
                                            ★ {{ ul.rating }}.0
                                        </span>
                                        <span
                                            :class="[
                                                'rounded-md px-2 py-0.5 font-bold uppercase text-[10px]',
                                                ul.label_sentimen === 'positif'
                                                    ? 'bg-[#0E7C86]/15 text-[#0A5F67]'
                                                    : ul.label_sentimen === 'negatif'
                                                      ? 'bg-[#F26B4F]/20 text-[#C2492E]'
                                                      : 'bg-gray-200 text-gray-700',
                                            ]"
                                        > {{ t("Sentimen:", "Sentiment:") }} {{ ul.label_sentimen }}
                                        </span>
                                    </div>
                                    <span class="text-[var(--warna-teks-redup)]">{{ ul.tanggal }}</span>
                                </div>

                                <p class="mt-2 leading-relaxed text-[var(--warna-teks)]">{{ ul.teks_ulasan }}</p>

                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-[var(--warna-teks-redup)]">
                                    <span>{{ t("Pemandangan:", "Scenery:") }} <strong>{{ ul.skor_pemandangan }}</strong></span> •
                                    <span>{{ t("Kebersihan:", "Cleanliness:") }} <strong>{{ ul.skor_kebersihan }}</strong></span> •
                                    <span>{{ t("Akses:", "Access:") }} <strong>{{ ul.skor_akses }}</strong></span> •
                                    <span>{{ t("Fasilitas:", "Facilities:") }} <strong>{{ ul.skor_fasilitas }}</strong></span> •
                                    <span>{{ t("Harga:", "Value:") }} <strong>{{ ul.skor_harga }}</strong></span>
                                </div>

                                <!-- Balasan Resmi Pengelola Destinasi -->
                                <div
                                    v-if="ul.balasan_pengelola"
                                    class="mt-3 rounded-lg border-l-4 border-[#0E7C86] bg-[#D5EEF0]/40 p-3 text-xs"
                                >
                                    <div class="font-bold text-[#0A5F67]">{{ t("Tanggapan Resmi Pengelola Destinasi:", "Destination manager response:") }}</div>
                                    <p class="mt-0.5 text-[var(--warna-teks)]">{{ ul.balasan_pengelola }}</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Kartu Pemesanan E-Tiket (Sticky) -->
                <aside class="lg:col-span-4">
                    <div class="kartu-togean sticky top-22 p-6">
                        <div class="flex items-center justify-between border-b border-[var(--warna-garis)] pb-3">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#0E7C86]">{{ (!destinasi.harga_tiket_dewasa || destinasi.harga_tiket_dewasa <= 0) ? t("INFORMASI KUNJUNGAN", "VISITOR INFORMATION") : t("LAYANAN E-TIKET RESMI", "OFFICIAL E-TICKET SERVICE") }}</span>
                                <h3 class="font-heading text-lg font-extrabold text-[var(--warna-teks)]"> {{ (!destinasi.harga_tiket_dewasa || destinasi.harga_tiket_dewasa <= 0) ? t("Akses & Operasional", "Access & Guidelines") : t("Pesan Tiket Kunjungan", "Plan your visit") }} </h3>
                            </div>
                            <Sparkles v-if="!destinasi.harga_tiket_dewasa || destinasi.harga_tiket_dewasa <= 0" class="h-6 w-6 text-emerald-600" />
                            <Ticket v-else class="h-6 w-6 text-[#F26B4F]" />
                        </div>

                        <!-- Opsi A: Destinasi Bebas Tiket Masuk / Akses Terbuka (Gratis) -->
                        <div v-if="!destinasi.harga_tiket_dewasa || destinasi.harga_tiket_dewasa <= 0" class="mt-4 space-y-4">
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-800 dark:bg-emerald-950/30">
                                <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                                    <Sparkles class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                    <strong class="text-xs font-bold">{{ t('Bebas Tiket Masuk (Gratis)', 'Free Public Access') }}</strong>
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-emerald-950/80 dark:text-emerald-200/80">
                                    {{ t(
                                        'Destinasi ini merupakan ruang publik / cagar budaya terbuka yang dapat dikunjungi langsung tanpa biaya tiket masuk atau reservasi berbayar.',
                                        'This destination is an open public space or cultural landmark that can be visited freely without admission fees or booking.'
                                    ) }}
                                </p>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between py-2 border-b border-garis">
                                    <span class="text-tinta-redup">{{ t('Status Akses', 'Access Status') }}</span>
                                    <span class="font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/50 dark:text-emerald-300 px-2.5 py-0.5 rounded-full text-[10.5px]">{{ t('Terbuka untuk Umum', 'Open to Public') }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-garis">
                                    <span class="text-tinta-redup">{{ t('Jam Buka Rekomendasi', 'Operating Hours') }}</span>
                                    <span class="font-bold text-tinta text-right">{{ destinasi.jam_operasional || 'Setiap Hari (06.00 – 18.00 WITA)' }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-garis">
                                    <span class="text-tinta-redup">{{ t('Tarif Masuk', 'Admission Fee') }}</span>
                                    <strong class="font-bold text-emerald-700 dark:text-emerald-300">{{ t('Rp 0 (Gratis)', 'IDR 0 (Free)') }}</strong>
                                </div>
                            </div>

                            <div class="pt-2 space-y-2.5">
                                <button
                                    type="button"
                                    @click="toggleWishlist"
                                    class="tombol-utama w-full text-xs min-h-[42px] cursor-pointer"
                                >
                                    <Heart class="h-4 w-4" :class="isWishlisted ? 'fill-white' : ''" />
                                    <span>{{ isWishlisted ? t('Tersimpan di Wishlist', 'Saved to Wishlist') : t('Simpan ke Wishlist Saya', 'Save to My Wishlist') }}</span>
                                </button>
                                <Link
                                    href="/itinerari"
                                    class="flex h-10 w-full items-center justify-center rounded-xl border border-garis bg-permukaan-redup text-xs font-bold text-tinta hover:bg-utama-muda/50 transition cursor-pointer"
                                >
                                    {{ t('+ Masukkan ke Rencana Perjalanan', '+ Add to Itinerary Plan') }}
                                </Link>
                            </div>
                        </div>

                        <!-- Opsi B: Destinasi Bertiket Resmi (Formulir Pemesanan E-Tiket) -->
                        <form v-else @submit.prevent="submitTiket" class="mt-4 space-y-4 text-xs">
                        <div v-if="Object.keys(ticketForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in ticketForm.errors" :key="field">{{ message }}</li></ul></div>
                            <div>
                                <label for="field-destinasi-show-ticketForm-tanggal_kunjungan" class="mb-1 block font-bold">{{ t("Tanggal Kunjungan", "Visit date") }}</label>
                                <input :aria-invalid="Boolean(ticketForm.errors.tanggal_kunjungan)" id="field-destinasi-show-ticketForm-tanggal_kunjungan"
                                    v-model="ticketForm.tanggal_kunjungan"
                                    type="date"
                                    required
                                    class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-sm"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="field-destinasi-show-ticketForm-jumlah_dewasa" class="mb-1 block font-bold">{{ t("Dewasa (", "Adults (") }}{{ formatCurrency(destinasi.harga_tiket_dewasa) }})</label>
                                    <input :aria-invalid="Boolean(ticketForm.errors.jumlah_dewasa)" id="field-destinasi-show-ticketForm-jumlah_dewasa"
                                        v-model.number="ticketForm.jumlah_dewasa"
                                        type="number"
                                        min="1"
                                        max="50"
                                        class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-sm font-bold tabular-nums"
                                    />
                                </div>
                                <div>
                                    <label for="field-destinasi-show-ticketForm-jumlah_anak" class="mb-1 block font-bold">{{ t("Anak (", "Children (") }}{{ formatCurrency(destinasi.harga_tiket_anak) }})</label>
                                    <input :aria-invalid="Boolean(ticketForm.errors.jumlah_anak)" id="field-destinasi-show-ticketForm-jumlah_anak"
                                        v-model.number="ticketForm.jumlah_anak"
                                        type="number"
                                        min="0"
                                        max="50"
                                        class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-sm font-bold tabular-nums"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="field-destinasi-show-ticketForm-metode_bayar" class="mb-1 block font-bold">{{ t("Metode Pembayaran", "Payment Method") }}</label>
                                <select :aria-invalid="Boolean(ticketForm.errors.metode_bayar)" id="field-destinasi-show-ticketForm-metode_bayar"
                                    v-model="ticketForm.metode_bayar"
                                    class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-sm font-semibold"
                                >
                                    <option value="QRIS">{{ t("QRIS Instan", "Instant QRIS") }}</option>
                                    <option value="E-Wallet">{{ t("E-Wallet", "E-wallet") }}</option>
                                    <option value="Transfer Bank">{{ t("Transfer Bank Virtual Account", "Bank virtual account") }}</option>
                                    <option value="Bayar di Loket">{{ t("Reservasi & Bayar di Loket", "Reserve and pay at the counter") }}</option>
                                </select>
                            </div>

                            <div class="rounded-xl bg-[#D5EEF0]/60 p-4">
                                <div class="flex items-center justify-between text-xs text-[#0A5F67]">
                                    <span>{{ t("Total Estimasi Pembayaran", "Estimated total") }}</span>
                                    <strong class="font-heading text-xl font-extrabold text-[#12344D] tabular-nums">
                                        {{ formatCurrency(totalHargaTiket) }}
                                    </strong>
                                </div>
                                <p class="mt-1 text-[11px] text-[#556877]"> {{ t("E-Tiket dengan kode QR unik langsung terbit dan dapat diverifikasi oleh Pengelola Destinasi.", "An official QR ticket will be issued for verification by the destination manager.") }} </p>
                            </div>

                            <!-- Satu-satunya Tombol Ajakan Karang per layar sesuai DESIGN.md Bagian 5 -->
                            <button
                                type="submit"
                                :disabled="ticketForm.processing"
                                class="tombol-ajakan w-full text-sm shadow-md"
                            >
                                {{ ticketForm.processing ? 'Memproses E-Tiket...' : 'Pesan E-Tiket Sekarang' }}
                            </button>

                            <Link
                                href="/itinerari"
                                class="flex h-11 w-full items-center justify-center rounded-xl border-2 border-[#0E7C86] text-xs font-bold text-[#0A5F67] transition hover:bg-[#D5EEF0]"
                            > {{ t("+ Masukkan ke Perencana Itinerari", "+ Add to itinerary planner") }} </Link>
                        </form>
                    </div>
                </aside>
            </div>

            <!-- Rekomendasi Destinasi Serupa / Terdekat -->
            <div v-if="destinasiSerupa.length" class="mt-12 border-t border-[var(--warna-garis)] pt-10">
                <h2 class="font-heading text-xl font-extrabold text-[var(--warna-teks)]"> {{ t("Destinasi Serupa & Terdekat", "More destinations to explore") }} </h2>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <KartuDestinasi catalog-view
                        v-for="ds in destinasiSerupa"
                        :key="ds.id"
                        :destinasi="ds"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

