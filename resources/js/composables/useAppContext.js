import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const currentLocale = ref('id');
const currentTheme = ref(typeof window !== 'undefined' ? (localStorage.getItem('jelajah_tema') || 'terang') : 'terang');

const translations = {
    id: {
        nav_beranda: 'Beranda',
        nav_jelajah: 'Jelajah Destinasi',
        nav_peta: 'Peta Interaktif',
        nav_statistik: 'Statistik Publik',
        nav_informasi: 'Event & Pengaduan',
        nav_wishlist: 'Wishlist',
        nav_itinerari: 'Perencana Itinerari',
        nav_tiket: 'Tiket Saya',
        nav_admin: 'Dashboard Eksekutif',
        nav_pengelola: 'Panel Pengelola',
        btn_masuk: 'Masuk Akun',
        btn_daftar: 'Daftar',
        btn_keluar: 'Keluar',
        hero_eyebrow: 'PORTAL RESMI & BIG DATA PARIWISATA SULAWESI TENGAH',
        hero_title: 'Menjelajahi Laut Togean hingga Warisan Megalitikum Lore Lindu',
        hero_sub: 'Temukan 27 destinasi terkurasi di 13 kabupaten/kota, pantau indikator keramaian harian, susun itinerari perjalanan, dan pesan e-tiket secara instan.',
        search_placeholder: 'Cari destinasi, pulau, danau, atau aktivitas...',
        all_categories: 'Semua Kategori',
        btn_cari: 'Telusuri Destinasi',
        section_unggulan: 'Destinasi Unggulan Terkurasi',
        section_angka: 'Sulawesi Tengah dalam Angka & Tren Kunjungan',
        section_event: 'Kalender Event & Agenda Budaya Terdekat',
        section_berita: 'Kabar & Panduan Wisata Resmi Dinas',
        crowd_sepi: 'Sepi',
        crowd_sedang: 'Sedang',
        crowd_ramai: 'Ramai',
        disclaimer_data: 'Pernyataan Integritas Data Resmi: Seluruh indikator kunjungan wisatawan (2022–2025) dan akomodasi perhotelan bersumber 100% dari Laporan Resmi Dinas Pariwisata Provinsi Sulawesi Tengah & Badan Pusat Statistik (BPS). Modul pemesanan tiket dan ulasan berstatus production-ready siap menerima transaksi riil.',
    },
    en: {
        nav_beranda: 'Home',
        nav_jelajah: 'Explore Destinations',
        nav_peta: 'Interactive Map',
        nav_statistik: 'Public Statistics',
        nav_informasi: 'Events & Feedback',
        nav_wishlist: 'Wishlist',
        nav_itinerari: 'Itinerary Planner',
        nav_tiket: 'My E-Tickets',
        nav_admin: 'Executive Dashboard',
        nav_pengelola: 'Manager Portal',
        btn_masuk: 'Sign In',
        btn_daftar: 'Register',
        btn_keluar: 'Sign Out',
        hero_eyebrow: 'OFFICIAL TOURISM PORTAL & BIG DATA ANALYTICS — CENTRAL SULAWESI',
        hero_title: 'From the Coral Reefs of Togean to the Megaliths of Lore Lindu',
        hero_sub: 'Explore 27 curated destinations across 13 regencies and cities, monitor live crowd levels, build custom itineraries, and book official QR e-tickets.',
        search_placeholder: 'Search destinations, islands, lakes, or activities...',
        all_categories: 'All Categories',
        btn_cari: 'Explore Now',
        section_unggulan: 'Featured Flagship Destinations',
        section_angka: 'Central Sulawesi Tourism in Numbers & Trends',
        section_event: 'Upcoming Cultural & Marine Events',
        section_berita: 'Official News & Travel Guides',
        crowd_sepi: 'Quiet',
        crowd_sedang: 'Moderate',
        crowd_ramai: 'Busy',
        disclaimer_data: 'Official Data Integrity Statement: All tourism visitor indicators (2022–2025) and hospitality metrics are 100% sourced from official publications of the Central Sulawesi Tourism Office and BPS. E-ticketing and review modules are production-ready for live visitor interactions.',
    },
};

export function getLocalDate(dayOffset = 0) {
    const date = new Date();
    date.setDate(date.getDate() + dayOffset);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

export function useAppContext() {
    const page = usePage();

    const locale = computed(() => {
        return page.props?.locale || currentLocale.value || 'id';
    });

    const isEn = computed(() => locale.value === 'en');
    const user = computed(() => page.props?.auth?.user || null);

    const t = (keyOrIdText, enText = null) => {
        const lang = locale.value === 'en' ? 'en' : 'id';
        if (enText !== null) {
            return lang === 'en' ? enText : keyOrIdText;
        }
        return translations[lang]?.[keyOrIdText] || translations.id[keyOrIdText] || keyOrIdText;
    };

    const setLocale = (newLoc) => {
        currentLocale.value = newLoc;
        document.documentElement.setAttribute('lang', newLoc);
        router.post('/locale', { locale: newLoc }, { preserveScroll: true, preserveState: true });
    };

    const toggleTheme = () => {
        const next = currentTheme.value === 'gelap' ? 'terang' : 'gelap';
        currentTheme.value = next;
        localStorage.setItem('jelajah_tema', next);
        document.documentElement.setAttribute('data-tema', next);
    };

    const formatNumber = (val) => {
        const num = Number(val || 0);
        return new Intl.NumberFormat(locale.value === 'en' ? 'en-US' : 'id-ID').format(num);
    };

    const formatCurrency = (val) => {
        if (val == null) return t('Belum tersedia', 'Unavailable');
        const num = Number(val || 0);
        if (num === 0) {
            return locale.value === 'en' ? 'Free' : 'Gratis';
        }
        return 'Rp ' + new Intl.NumberFormat(locale.value === 'en' ? 'en-US' : 'id-ID').format(num);
    };

    const formatRupiah = (val) => {
        if (val == null) return t('Belum tersedia', 'Unavailable');
        const num = Number(val || 0);
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    };

    const formatDate = (val) => {
        if (!val) return '-';
        const str = String(val);
        const d = new Date(str);
        if (Number.isNaN(d.getTime())) {
            return str.slice(0, 10);
        }
        return d.toLocaleDateString(locale.value === 'en' ? 'en-US' : 'id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    };

    const navigateWithAuth = (targetUrl) => {
        if (!user.value) {
            router.visit(`/login?redirect=${encodeURIComponent(targetUrl)}&reason=detail`);
            return false;
        }
        router.visit(targetUrl);
        return true;
    };

    return {
        locale,
        isEn,
        user,
        theme: currentTheme,
        t,
        setLocale,
        toggleTheme,
        navigateWithAuth,
        formatNumber,
        formatCurrency,
        formatRupiah,
        formatDate,
    };
}
