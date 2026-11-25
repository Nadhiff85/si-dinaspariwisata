<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import {
    Search, X, ShieldCheck, BarChart3, Building2, Compass, Map as MapIcon,
    Sparkles, Database, FileDown, RefreshCw, Sun, Moon, Users, ArrowRight
} from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open']);

const page = usePage();
const { user, theme, locale, t, toggleTheme, setLocale, formatRupiah } = useAppContext();

const query = ref('');
const selectedIndex = ref(0);
const inputRef = ref(null);

const isOpen = computed({
    get: () => props.open,
    set: (val) => emit('update:open', val),
});

function closePalette() {
    isOpen.value = false;
    query.value = '';
}

watch(isOpen, async (val) => {
    if (val) {
        selectedIndex.value = 0;
        await nextTick();
        inputRef.value?.focus();
    }
});

const destinations = computed(() => page.props.quickDestinations || []);

const allCommands = computed(() => {
    const items = [];
    const isAdmin = user.value?.peran === 'admin';
    const isPengelola = user.value?.peran === 'pengelola_destinasi';

    if (isAdmin) {
        items.push(
            {
                id: 'adm-briefing',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Ringkasan Eksekutif & Briefing Pagi', 'Executive Morning Briefing'),
                subtitle: t('KPI utama, proyeksi 6 bulan, rekomendasi prioritas & peringkat 27 destinasi', 'Key KPIs, 6-month forecast, priority actions & 27-spot ranking'),
                badge: 'Utama',
                icon: ShieldCheck,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'briefing' }),
            },
            {
                id: 'adm-kunjungan',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Analitik Kunjungan & Geospasial Provinsi', 'Visitation & Provincial Geospatial Analytics'),
                subtitle: t('5 Visualisasi: Proyeksi SARIMAX (#13), Tren 5 Tahun (#14), Peta (#15), Treemap (#16), Musiman (#19)', '5 Charts: SARIMAX (#13), 5Y Area (#14), Choropleth (#15), Treemap (#16), Seasonality (#19)'),
                badge: '5 Chart',
                icon: BarChart3,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'kunjungan' }),
            },
            {
                id: 'adm-unggulan',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Evaluasi Skor Unggulan, Kuadran & Konversi', 'Composite Score, Quadrant & Funnel Analysis'),
                subtitle: t('4 Visualisasi: Skor Komposit (#17), Matriks 4 Kuadran (#18), Funnel (#27), Box Plot (#28)', '4 Charts: Top Score (#17), 4-Quadrant Bubble (#18), Funnel (#27), Box Plot (#28)'),
                badge: '4 Chart',
                icon: Sparkles,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'unggulan' }),
            },
            {
                id: 'adm-pasar',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Pasar Wisatawan & Akomodasi Perhotelan', 'Tourist Origin Markets & Hotel Occupancy'),
                subtitle: t('4 Visualisasi: Korelasi TPK Hotel (#20), Tamu Wilayah (#21), Negara Wisman (#22), Provinsi Wisnus (#23)', '4 Charts: TPK Combo (#20), Regional Guests (#21), Intl Markets (#22), Domestic Markets (#23)'),
                badge: '4 Chart',
                icon: Building2,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'pasar' }),
            },
            {
                id: 'adm-sentimen',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Sentimen Ulasan & Profil 5 Aspek Kualitas', 'Review Sentiment & 5-Aspect Quality Profile'),
                subtitle: t('3 Visualisasi: Distribusi Sentimen (#24), Tren Kepuasan (#25), Radar 5 Aspek Kategori (#26)', '3 Charts: Sentiment Stack (#24), Rating Trend (#25), Category Radar (#26)'),
                badge: '3 Chart',
                icon: Sparkles,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'sentimen' }),
            },
            {
                id: 'adm-objek',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Supervisi 6 Chart Operasional per Objek Wisata', 'Operational 6-Chart Supervision per Spot'),
                subtitle: t('6 Visualisasi: Harian 30 Hari (#8), MoM/YoY (#9), Pie (#10), Tiket (#11), Sentimen (#12), Radar (#30)', '6 Charts: 30-Day Trend (#8), MoM/YoY (#9), Pie (#10), Tickets (#11), Sentiment (#12), Radar (#30)'),
                badge: '6 Chart',
                icon: Building2,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'objek' }),
            },
            {
                id: 'adm-publik',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Visualisasi Statistik Publik & Wisatawan', 'Public & Tourist Education Charts'),
                subtitle: t('8 Visualisasi: Kategori (#1), Top Rating (#2), Tren (#3), Waktu Terbaik (#4), Peta (#5), Gauge (#6), Radar (#7), Tarif (#29)', '8 Public Charts (#1 to #7 & #29)'),
                badge: '8 Chart',
                icon: Compass,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'publik' }),
            },
            {
                id: 'adm-keputusan',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Pemesanan, kapasitas dan retensi', 'Bookings, capacity and retention'),
                subtitle: t('6 analisis: lama menginap, jarak pemesanan, pembatalan, check-in, kapasitas dan pemesanan ulang', '6 analyses: length of stay, booking lead time, cancellations, check-ins, capacity and repeat bookings'),
                badge: '#31–36',
                icon: BarChart3,
                action: () => router.get('/admin/dashboard', { tab: 'ringkasan', sub: 'keputusan' }),
            },
            {
                id: 'adm-rekomendasi',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Sistem Pendukung Keputusan & Rekomendasi Kebijakan', 'Decision Support & Policy Recommendations'),
                subtitle: t('Daftar aksi kebijakan berbasis aturan dari anomali data & sentimen', 'Rule-based policy actions from forecast & sentiment anomalies'),
                badge: 'Kebijakan',
                icon: Sparkles,
                action: () => router.get('/admin/dashboard', { tab: 'rekomendasi' }),
            },
            {
                id: 'adm-master',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Kendali 27 Objek Wisata, Akun Pengelola & Persetujuan Tarif', '27 Destinations, Manager Accounts & Tariff Approvals'),
                subtitle: t('Pantau 27 akun pengelola khusus dan setujui perubahan profil/tarif', 'Monitor 27 manager accounts and approve tariff/profile proposals'),
                badge: '27 Akun',
                icon: Building2,
                action: () => router.get('/admin/dashboard', { tab: 'master' }),
            },
            {
                id: 'adm-warehouse',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Gudang Data Star Schema, Kalibrasi Bobot & Audit 14 Sheet', 'Star Schema DW, Weight Calibration & 14-Sheet Audit'),
                subtitle: t('Verifikasi integritas 17.780+ baris Excel terhadap MySQL & Data Warehouse', 'Verify 17,780+ Excel records against MySQL & Data Warehouse'),
                badge: 'Gudang Data',
                icon: Database,
                action: () => router.get('/admin/dashboard', { tab: 'warehouse' }),
            },
            {
                id: 'adm-moderasi',
                group: t('Sub-Halaman Dashboard Akun Utama', 'Executive Dashboard Sub-Pages'),
                title: t('Manajemen Pengguna, Moderasi Ulasan & Log Audit', 'User Management, Review Moderation & Audit Logs'),
                subtitle: t('Kelola hak akses, tanggapi pengaduan fasilitas, setujui event & berita', 'Manage roles, respond to facility complaints, approve events & news'),
                badge: 'Moderasi',
                icon: Users,
                action: () => router.get('/admin/dashboard', { tab: 'moderasi' }),
            }
        );
    }

    // 27 Destinations
    for (const d of destinations.value) {
        items.push({
            id: `dest-${d.id}`,
            group: t('27 Objek Wisata Sulawesi Tengah', '27 Central Sulawesi Tourism Destinations'),
            title: `#${d.id} ${d.nama_destinasi}`,
            subtitle: `${d.wilayah?.nama_wilayah || 'Sulawesi Tengah'} • ${d.kategori} • Tiket ${formatRupiah(d.harga_tiket_dewasa)}`,
            badge: isAdmin || isPengelola ? t('Buka Panel Pengelola →', 'Open Manager Panel →') : d.kategori,
            icon: MapIcon,
            action: () => {
                if (isAdmin) {
                    router.get('/pengelola/dashboard', { destinasi_id: d.id });
                } else {
                    router.get(`/destinasi/${d.slug || d.id}`);
                }
            },
        });
    }

    // Quick Navigation & System Actions
    items.push(
        {
            id: 'nav-beranda',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: t('Beranda Portal Wisatawan', 'Public Tourism Home'),
            subtitle: t('Halaman depan utama Jelajah Sulteng', 'Main public landing page'),
            badge: 'Halaman',
            icon: Compass,
            action: () => router.get('/'),
        },
        {
            id: 'nav-peta',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: t('Peta Geospasial 13 Kabupaten/Kota', '13 Regencies Geospatial Map'),
            subtitle: t('Eksplorasi lokasi 27 destinasi pada peta interaktif', 'Explore all 27 destinations on the interactive map'),
            badge: 'Peta',
            icon: MapIcon,
            action: () => router.get('/peta'),
        },
        {
            id: 'nav-statistik',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: t('Halaman Statistik Publik (8 Chart)', 'Public Statistics Page (8 Charts)'),
            subtitle: t('Infografis publik untuk wisatawan & peneliti', 'Public analytics for tourists & researchers'),
            badge: 'Publik',
            icon: BarChart3,
            action: () => router.get('/statistik'),
        },
        {
            id: 'nav-login',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: t('Ganti Akun (Akun Utama / 27 Akun Pengelola Objek)', 'Switch Account (Main Admin / 27 Spot Managers)'),
            subtitle: t('Buka halaman pemilihan cepat 1 Akun Utama & 27 Akun Objek Wisata', 'Open 1-click account switcher for Main Admin & 27 Spot Managers'),
            badge: 'Akun',
            icon: ShieldCheck,
            action: () => router.get('/login'),
        },
        {
            id: 'act-theme',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: theme.value === 'gelap' ? t('Beralih ke Mode Terang (Pasir Togean)', 'Switch to Light Mode') : t('Beralih ke Mode Gelap (Samudra Malam)', 'Switch to Dark Mode'),
            subtitle: t('Ubah skema warna antarmuka seketika', 'Toggle interface color theme immediately'),
            badge: 'Tema',
            icon: theme.value === 'gelap' ? Sun : Moon,
            action: () => toggleTheme(),
        },
        {
            id: 'act-locale',
            group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
            title: locale.value === 'id' ? 'Switch Interface Language to English (EN)' : 'Ubah Bahasa Antarmuka ke Bahasa Indonesia (ID)',
            subtitle: t('Dukungan dwibahasa penuh ID & EN', 'Full bilingual support ID & EN'),
            badge: locale.value === 'id' ? 'EN' : 'ID',
            icon: Compass,
            action: () => setLocale(locale.value === 'id' ? 'en' : 'id'),
        }
    );

    if (isAdmin) {
        items.push(
            {
                id: 'act-etl',
                group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
                title: t('Jalankan Sinkronisasi ETL Gudang Data Sekarang', 'Run Data Warehouse ETL Synchronization Now'),
                subtitle: t('Mutakhirkan Star Schema, prediksi SARIMAX, dan skor destinasi', 'Refresh Star Schema, SARIMAX forecast, and composite scores'),
                badge: 'ETL',
                icon: RefreshCw,
                action: () => router.post('/admin/etl/run'),
            },
            {
                id: 'act-pdf',
                group: t('Navigasi & Tindakan Cepat', 'Navigation & Quick Actions'),
                title: t('Unduh Laporan Eksekutif Provinsi (PDF)', 'Download Provincial Executive Report (PDF)'),
                subtitle: t('Cetak ringkasan resmi indikator kinerja pariwisata', 'Print official tourism KPI executive summary'),
                badge: 'PDF',
                icon: FileDown,
                action: () => window.open('/admin/ekspor/pdf', '_blank'),
            }
        );
    }

    return items;
});

const filteredCommands = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) {
        return allCommands.value.slice(0, 20);
    }
    return allCommands.value.filter(
        (item) =>
            item.title.toLowerCase().includes(q) ||
            item.subtitle.toLowerCase().includes(q) ||
            item.group.toLowerCase().includes(q) ||
            String(item.badge || '').toLowerCase().includes(q)
    );
});

const groupedCommands = computed(() => {
    const groups = [];
    const map = new Map();
    filteredCommands.value.forEach((item, flatIndex) => {
        if (!map.has(item.group)) {
            const g = { name: item.group, items: [] };
            map.set(item.group, g);
            groups.push(g);
        }
        map.get(item.group).items.push({ ...item, flatIndex });
    });
    return groups;
});

watch(query, () => {
    selectedIndex.value = 0;
});

function executeItem(item) {
    if (!item) return;
    closePalette();
    item.action();
}

function handleKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        isOpen.value = !isOpen.value;
        return;
    }

    if (!isOpen.value) return;

    if (e.key === 'Escape') {
        e.preventDefault();
        closePalette();
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (filteredCommands.value.length > 0) {
            selectedIndex.value = (selectedIndex.value + 1) % filteredCommands.value.length;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (filteredCommands.value.length > 0) {
            selectedIndex.value = (selectedIndex.value - 1 + filteredCommands.value.length) % filteredCommands.value.length;
        }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const chosen = filteredCommands.value[selectedIndex.value];
        if (chosen) executeItem(chosen);
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/55 px-4 pt-[10vh] backdrop-blur-xs"
            @click.self="closePalette"
        >
            <div
                class="kartu-togean w-full max-w-2xl overflow-hidden shadow-angkat muncul-halus"
                role="dialog"
                aria-modal="true"
                aria-label="Pencarian & Komando Cepat"
            >
                <!-- Input Bar -->
                <div class="flex items-center gap-3 border-b border-garis px-4 py-3.5 bg-permukaan">
                    <Search class="h-5 w-5 shrink-0 text-utama" />
                    <input
                        ref="inputRef"
                        v-model="query"
                        type="text"
                        :placeholder="t('Ketik nama objek wisata (Togean, Poso...), sub-halaman analitik, atau aksi...', 'Type destination name, analytics view, or action...')"
                        class="w-full bg-transparent text-sm font-medium text-tinta placeholder:text-tinta-samar focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="closePalette"
                        class="rounded-lg p-1 text-tinta-redup hover:bg-utama-muda/50 hover:text-tinta cursor-pointer"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Results List -->
                <div class="max-h-[60vh] overflow-y-auto p-2.5 gulir-tipis bg-permukaan">
                    <div v-if="groupedCommands.length === 0" class="py-10 text-center text-xs text-tinta-redup">
                        {{ t('Tidak ditemukan hasil yang cocok dengan kata kunci tersebut.', 'No matching destinations or commands found.') }}
                    </div>

                    <div v-for="grp in groupedCommands" :key="grp.name" class="mb-3 last:mb-0">
                        <div class="px-3 py-1.5 text-[10.5px] font-bold uppercase tracking-wider text-tinta-samar">
                            {{ grp.name }}
                        </div>
                        <div class="space-y-0.5">
                            <button
                                v-for="item in grp.items"
                                :key="item.id"
                                type="button"
                                @click="executeItem(item)"
                                @mouseenter="selectedIndex = item.flatIndex"
                                :class="[
                                    'flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left transition cursor-pointer',
                                    selectedIndex === item.flatIndex
                                        ? 'bg-utama text-white'
                                        : 'text-tinta hover:bg-utama-muda/40',
                                ]"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        :class="[
                                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                            selectedIndex === item.flatIndex
                                                ? 'bg-white/20 text-white'
                                                : 'bg-utama-muda text-utama-teks',
                                        ]"
                                    >
                                        <component :is="item.icon" class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-xs font-bold">
                                            {{ item.title }}
                                        </div>
                                        <div
                                            :class="[
                                                'truncate text-[11px]',
                                                selectedIndex === item.flatIndex ? 'text-white/85' : 'text-tinta-redup',
                                            ]"
                                        >
                                            {{ item.subtitle }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <span
                                        v-if="item.badge"
                                        :class="[
                                            'rounded-md px-2 py-0.5 text-[10px] font-bold',
                                            selectedIndex === item.flatIndex
                                                ? 'bg-white/20 text-white'
                                                : 'bg-permukaan-redup text-tinta-redup border border-garis',
                                        ]"
                                    >
                                        {{ item.badge }}
                                    </span>
                                    <ArrowRight class="h-3.5 w-3.5 opacity-75" />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Keyboard Hints -->
                <div class="flex items-center justify-between border-t border-garis bg-permukaan-redup px-4 py-2.5 text-[11px] text-tinta-redup">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1">
                            <kbd class="kbd">↑</kbd>
                            <kbd class="kbd">↓</kbd>
                            <span>Navigasi</span>
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="kbd">↵</kbd>
                            <span>Buka</span>
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <kbd class="kbd">Esc</kbd>
                            <span>Tutup</span>
                        </span>
                    </div>
                    <span class="font-medium text-utama-teks">27 Objek Wisata & 30 Visualisasi</span>
                </div>
            </div>
        </div>
    </Teleport>
</template>

