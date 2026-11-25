<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KartuDestinasi from '@/Components/destinasi/KartuDestinasi.vue';
import { useAppContext } from '@/composables/useAppContext';
import {
    Search, MapPin, Calendar, Newspaper, Users, Star,
    ArrowRight, X, Play, Pause
} from 'lucide-vue-next';

const props = defineProps({
    destinasiUnggulan: { type: Array, default: () => [] },
    eventTerdekat: { type: Array, default: () => [] },
    beritaTerbaru: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    kategoriList: { type: Array, default: () => [] },
    userWishlistIds: { type: Array, default: () => [] },
});

const { locale, user, t, navigateWithAuth, formatCurrency } = useAppContext();

const searchQuery = ref('');
const selectedKategori = ref('SEMUA');
const activeUnggulanFilter = ref('SEMUA');

// =========================================================================
// VIDEO LATAR HERO (4 destinasi ikon Sulawesi Tengah, berganti tiap 12 detik)
// =========================================================================
const heroShowcases = [
    {
        id: 1,
        labelPendek: 'Togean',
        nama: 'Kepulauan Togean',
        wilayah: 'Tojo Una-Una',
        foto: '/images/destinasi/togean-hero.jpg',
        video: '/videos/togean-aerial.webm',
        slug: '/destinasi/kepulauan-togean-1',
    },
    {
        id: 2,
        labelPendek: 'Lore Lindu',
        nama: 'Taman Nasional Lore Lindu',
        wilayah: 'Sigi & Poso',
        foto: '/images/destinasi/lore-lindu.jpg',
        video: '/videos/lore-lindu-aerial.webm',
        slug: '/destinasi/taman-nasional-lore-lindu-2',
    },
    {
        id: 3,
        labelPendek: 'Danau Poso',
        nama: 'Danau Poso',
        wilayah: 'Poso',
        foto: '/images/destinasi/danau-poso.jpg',
        video: '/videos/danau-poso-aerial.webm',
        slug: '/destinasi/danau-poso-3',
    },
    {
        id: 4,
        labelPendek: 'Talise',
        nama: 'Pantai Talise',
        wilayah: 'Kota Palu',
        foto: '/images/destinasi/pantai-talise.jpg',
        video: '/videos/pantai-talise-aerial.webm',
        slug: '/destinasi/pantai-talise-4',
    },
];

const activeVideoIdx = ref(0);
const isBgVideoPaused = ref(false);
const bgVideoRef = ref(null);
const heroSectionRef = ref(null);
const heroCanvasRef = ref(null);
const videoLayerRef = ref(null);
const videoProgressRef = ref(null);
let showcaseElapsed = 0;
const setVideoProgressRef = (element) => {
    if (element) videoProgressRef.value = element;
};

// Interaksi Latar Belakang: Riak Air & Paralaks (Mouse kursor & sentuhan jari HP)
const pointerState = { x: 0, y: 0, nx: 0, ny: 0, lastX: -999, lastY: -999, inside: false };
let currentParallaxX = 0;
let currentParallaxY = 0;
const ripples = [];
let animationFrameId = null;

const spawnRipple = (x, y, count = 1, maxRadius = 72) => {
    for (let i = 0; i < count; i++) {
        ripples.push({
            x,
            y,
            r: 4 + i * 8,
            maxR: maxRadius + i * 26,
            speed: 1.9 + i * 0.35,
            alpha: count > 1 ? 0.48 - i * 0.10 : 0.22,
        });
    }
    if (ripples.length > 42) {
        ripples.splice(0, ripples.length - 42);
    }
};

const updatePointerCoords = (e) => {
    const el = heroSectionRef.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    pointerState.x = x;
    pointerState.y = y;
    pointerState.nx = ((x / (rect.width || 1)) - 0.5) * 2;
    pointerState.ny = ((y / (rect.height || 1)) - 0.5) * 2;
    pointerState.inside = true;
    return { x, y };
};

const onHeroPointerMove = (e) => {
    const pos = updatePointerCoords(e);
    if (!pos) return;

    const dist = Math.hypot(pos.x - pointerState.lastX, pos.y - pointerState.lastY);
    if (dist < 14) return;
    pointerState.lastX = pos.x;
    pointerState.lastY = pos.y;
    spawnRipple(pos.x, pos.y, 1, 68);
};

const onHeroPointerDown = (e) => {
    const pos = updatePointerCoords(e);
    if (!pos) return;
    spawnRipple(pos.x, pos.y, 3, 96);
};

const onHeroPointerLeave = () => {
    pointerState.inside = false;
    pointerState.nx = 0;
    pointerState.ny = 0;
    pointerState.lastX = -999;
    pointerState.lastY = -999;
};

const currentBgVideo = computed(() => heroShowcases[activeVideoIdx.value] || heroShowcases[0]);

const selectBgVideo = (idx) => {
    activeVideoIdx.value = idx;
    showcaseElapsed = 0;
    isBgVideoPaused.value = false;
};

const toggleBgVideoPlayback = () => {
    const vid = bgVideoRef.value;
    if (!vid) return;
    if (vid.paused) {
        vid.play();
        isBgVideoPaused.value = false;
    } else {
        vid.pause();
        isBgVideoPaused.value = true;
    }
};

// Filter Kategori Instan pada Seksi 2 (Destinasi Unggulan)
const filteredUnggulan = computed(() => {
    if (activeUnggulanFilter.value === 'SEMUA') {
        return props.destinasiUnggulan;
    }
    const matches = props.destinasiUnggulan.filter((d) => d.kategori === activeUnggulanFilter.value);
    return matches.length > 0 ? matches : props.destinasiUnggulan;
});

// Rotasi otomatis video latar + loop animasi interaktif kursor/sentuhan 60fps
let resizeHeroCanvas = null;
onMounted(() => {
    const canvas = heroCanvasRef.value;
    if (canvas && typeof window !== 'undefined') {
        const ctx = canvas.getContext('2d');
        resizeHeroCanvas = () => {
            if (!canvas.parentElement) return;
            canvas.width = canvas.parentElement.clientWidth;
            canvas.height = canvas.parentElement.clientHeight;
        };
        resizeHeroCanvas();
        window.addEventListener('resize', resizeHeroCanvas);

        let lastFrameTime = performance.now();
        const renderFrame = (timestamp) => {
            const elapsed = Math.min(timestamp - lastFrameTime, 100);
            lastFrameTime = timestamp;
            if (!isBgVideoPaused.value && !document.hidden) {
                showcaseElapsed += elapsed;
                if (showcaseElapsed >= 12000) {
                    selectBgVideo((activeVideoIdx.value + 1) % heroShowcases.length);
                }
            }
            if (videoProgressRef.value) {
                videoProgressRef.value.style.transform = `scaleX(${showcaseElapsed / 12000})`;
            }
            // Paralaks halus lerp pada layer video latar
            const targetX = pointerState.nx * -14;
            const targetY = pointerState.ny * -10;
            currentParallaxX += (targetX - currentParallaxX) * 0.08;
            currentParallaxY += (targetY - currentParallaxY) * 0.08;

            if (videoLayerRef.value) {
                videoLayerRef.value.style.transform = `translate3d(${currentParallaxX.toFixed(2)}px, ${currentParallaxY.toFixed(2)}px, 0) scale(1.04)`;
            }

            if (ctx && canvas.width > 0) {
                const w = canvas.width;
                const h = canvas.height;
                ctx.clearRect(0, 0, w, h);

                // Gambar riak air konsentris pirus & putih halus
                for (let i = ripples.length - 1; i >= 0; i--) {
                    const rp = ripples[i];
                    rp.r += rp.speed;
                    rp.alpha *= 0.962;

                    ctx.beginPath();
                    ctx.arc(rp.x, rp.y, rp.r, 0, Math.PI * 2);
                    ctx.strokeStyle = '#7DE2E8';
                    ctx.globalAlpha = rp.alpha;
                    ctx.lineWidth = 1.6;
                    ctx.stroke();

                    if (rp.r > 16) {
                        ctx.beginPath();
                        ctx.arc(rp.x, rp.y, rp.r * 0.62, 0, Math.PI * 2);
                        ctx.strokeStyle = '#FFFFFF';
                        ctx.globalAlpha = rp.alpha * 0.45;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }

                    if (rp.alpha < 0.025 || rp.r > rp.maxR) {
                        ripples.splice(i, 1);
                    }
                }

                ctx.globalAlpha = 1;
                ctx.shadowBlur = 0;
            }

            animationFrameId = requestAnimationFrame(renderFrame);
        };
        animationFrameId = requestAnimationFrame(renderFrame);
    }
});

onBeforeUnmount(() => {
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
    if (resizeHeroCanvas && typeof window !== 'undefined') {
        window.removeEventListener('resize', resizeHeroCanvas);
    }
});

const handleSearch = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('q', searchQuery.value);
    if (selectedKategori.value) params.set('kategori', selectedKategori.value);
    const target = `/destinasi${params.toString() ? '?' + params.toString() : ''}`;
    router.visit(target);
};

const openEventDetail = (ev) => {
    activeEventModal.value = ev;
};

const openBeritaDetail = (br) => {
    activeBeritaModal.value = br;
};

const activeEventModal = ref(null);
const activeBeritaModal = ref(null);

const MONTHS_SHORT_ID = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
const MONTHS_FULL_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function parseDateParts(dateStr) {
    if (!dateStr) return { day: '14', monthShort: 'NOV', full: '-' };
    const parts = String(dateStr).slice(0, 10).split('-');
    if (parts.length < 3) return { day: '14', monthShort: 'NOV', full: String(dateStr) };
    const y = parts[0];
    const mIdx = Math.max(0, Math.min(11, parseInt(parts[1], 10) - 1));
    const d = parts[2];
    return {
        day: d,
        monthShort: MONTHS_SHORT_ID[mIdx],
        full: `${parseInt(d, 10)} ${MONTHS_FULL_ID[mIdx]} ${y}`,
    };
}

function getEventDurationDays(startStr, endStr) {
    if (!startStr || !endStr) return 1;
    const s = new Date(startStr);
    const e = new Date(endStr);
    const diff = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;
    return diff > 0 ? diff : 1;
}

function resolveEventPhoto(ev, idx = 0) {
    const nama = String(ev?.nama_event_id || '').toLowerCase();
    if (nama.includes('togean') || nama.includes('ocean') || nama.includes('bahari')) return '/images/destinasi/togean-hero.jpg';
    if (nama.includes('poso') || nama.includes('danau')) return '/images/destinasi/danau-poso.jpg';
    if (nama.includes('palu') || nama.includes('talise') || nama.includes('kaledo')) return '/images/destinasi/pantai-talise.jpg';
    if (nama.includes('lore') || nama.includes('megalit') || nama.includes('adat')) return '/images/destinasi/lore-lindu.jpg';
    const pool = ['/images/destinasi/togean-hero.jpg', '/images/destinasi/danau-poso.jpg', '/images/destinasi/lore-lindu.jpg', '/images/destinasi/pantai-talise.jpg'];
    return pool[idx % pool.length];
}

function resolveBeritaPhoto(br, idx = 0) {
    const judul = String(br?.judul_id || '').toLowerCase();
    if (judul.includes('togean')) return '/images/destinasi/togean-hero.jpg';
    if (judul.includes('hotel') || judul.includes('phri') || judul.includes('palu')) return '/images/destinasi/pantai-talise.jpg';
    if (judul.includes('dashboard') || judul.includes('analitik') || judul.includes('unggulan')) return '/images/destinasi/lore-lindu.jpg';
    const pool = ['/images/destinasi/pantai-talise.jpg', '/images/destinasi/lore-lindu.jpg', '/images/destinasi/togean-hero.jpg', '/images/destinasi/danau-poso.jpg'];
    return pool[idx % pool.length];
}
</script>

<template>
    <AppLayout>
        <Head title="Beranda Resmi Pariwisata Sulawesi Tengah" />

        <!-- =============================================================== -->
        <!-- HERO: video udara, riak air, dan paralaks -->
        <!-- =============================================================== -->
        <section
            ref="heroSectionRef"
            @pointermove="onHeroPointerMove"
            @pointerdown="onHeroPointerDown"
            @pointerleave="onHeroPointerLeave"
            style="touch-action: pan-y;"
            class="relative isolate w-full overflow-hidden bg-[#0B1F2E] text-white"
        >
            <!-- Wrapper video dengan overscan -inset-4 agar aman bergeser saat efek paralaks -->
            <div ref="videoLayerRef" class="pointer-events-none absolute -inset-4 -z-20 will-change-transform">
                <Transition
                    enter-active-class="transition-opacity duration-1000"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-1000 absolute inset-0"
                    leave-to-class="opacity-0"
                >
                    <video
                        ref="bgVideoRef"
                        :key="currentBgVideo.video"
                        :poster="currentBgVideo.foto"
                        autoplay
                        muted
                        loop
                        playsinline
                        aria-hidden="true"
                        class="h-full w-full object-cover"
                    >
                        <source :src="currentBgVideo.video" type="video/webm" />
                    </video>
                </Transition>
            </div>

            <!-- Scrim: gelap di kiri bawah tempat teks berada, bening di kanan agar pemandangan terlihat -->
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(11,31,46,0.78)_0%,rgba(11,31,46,0.45)_45%,rgba(11,31,46,0)_80%),linear-gradient(0deg,rgba(11,31,46,0.6)_0%,rgba(11,31,46,0)_45%)]"
            ></div>

            <!-- Kanvas interaktif kursor/sentuhan (mode riak air & paralaks) -->
            <canvas
                ref="heroCanvasRef"
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 z-0 h-full w-full"
            ></canvas>

            <div class="relative z-10 mx-auto flex w-full max-w-[1440px] flex-col justify-center gap-7 px-4 pt-10 pb-6 sm:px-6 lg:min-h-[540px] lg:gap-8 lg:px-8 lg:pt-10 lg:pb-8">
                <div class="grid grid-cols-1 items-end gap-6 lg:grid-cols-12 lg:gap-8">
                    <!-- Kiri: Judul, Subjudul, dan Pencarian Utama -->
                    <div class="lg:col-span-8 muncul-halus">
                        <h1 class="font-heading text-[32px] font-bold leading-[1.08] tracking-tight text-white [text-shadow:0_2px_16px_rgba(11,31,46,0.65)] sm:text-5xl xl:text-[56px]">
                            <span class="block">{{ t('Jelajahi Pesona', 'Discover') }}</span>
                            <span class="block">{{ t('Sulawesi Tengah', 'Central Sulawesi') }}</span>
                        </h1>

                        <p class="mt-4 max-w-[52ch] text-sm leading-relaxed text-white/90 [text-shadow:0_1px_8px_rgba(11,31,46,0.65)] sm:text-base">
                            {{ t('Temukan 27 destinasi, susun perjalanan, dan jelajahi kekayaan alam serta budaya Sulawesi Tengah.', 'Explore 27 destinations, plan your trip, and discover the nature and culture of Central Sulawesi.') }}
                        </p>

                        <form
                            @submit.prevent="handleSearch"
                            class="mt-6 flex flex-col gap-2 rounded-2xl border border-white/40 bg-white p-2 shadow-[0_8px_24px_rgba(11,31,46,0.18)] sm:flex-row sm:items-center"
                        >
                            <label for="hero-cari" class="sr-only">{{ t('Cari destinasi wisata', 'Search destinations') }}</label>
                            <div class="relative flex-1 min-w-0">
                                <Search class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#556877]" />
                                <input
                                    id="hero-cari"
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="t('Ingin ke mana?', 'Where to?')"
                                    class="h-12 w-full rounded-xl bg-transparent pr-3 pl-10 text-sm text-[#12344D] placeholder-[#556877] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0E7C86]"
                                />
                            </div>
                            <label for="hero-kategori" class="sr-only">{{ t('Kategori wisata', 'Tourism category') }}</label>
                            <select
                                id="hero-kategori"
                                v-model="selectedKategori"
                                class="h-12 w-full shrink-0 rounded-xl border border-[#E3DACB] bg-[#FAF6EE] px-3 text-sm font-medium text-[#12344D] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0E7C86] sm:w-44 cursor-pointer"
                            >
                                <option value="SEMUA">{{ t('Semua kategori', 'All categories') }}</option>
                                <option v-for="kat in kategoriList" :key="kat" :value="kat">{{ kat }}</option>
                            </select>
                            <button
                                type="submit"
                                class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#F26B4F] px-4 text-sm font-bold text-[#0B1F2E] whitespace-nowrap transition hover:bg-[#FF8A70] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0E7C86] active:scale-[0.98] cursor-pointer"
                            >
                                <span>{{ t('Cari Destinasi', 'Find Destinations') }}</span>
                                <ArrowRight class="h-4 w-4" />
                            </button>
                        </form>
                    </div>

                    <!-- Kanan: Kartu Sorotan Destinasi Video Aktif (Menyeimbangkan Sisi Kanan Hero) -->
                    <div class="hidden lg:col-span-4 lg:block">
                        <div
                            class="relative isolate flex min-h-[292px] flex-col justify-end overflow-hidden rounded-2xl border border-white/35 bg-[#0B1F2E] p-5 text-white"
                        >
                            <img
                                :src="currentBgVideo.foto"
                                :alt="currentBgVideo.nama"
                                class="absolute inset-0 -z-20 h-full w-full object-cover"
                            />
                            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(11,31,46,0.15)_15%,rgba(11,31,46,0.95)_100%)]"></div>
                            <span class="absolute top-4 left-4 rounded-full border border-white/30 bg-[#0B1F2E]/75 px-3 py-1.5 text-[11px] font-semibold text-white">
                                {{ t('Sorotan destinasi', 'Destination spotlight') }}
                            </span>
                            <span class="flex items-center gap-1.5 text-xs font-medium text-[#A8E6EB]">
                                <MapPin class="h-3.5 w-3.5 shrink-0" /> {{ currentBgVideo.wilayah }}
                            </span>
                            <h2 class="font-heading mt-2 text-xl font-bold leading-tight text-white xl:text-2xl">
                                {{ currentBgVideo.nama }}
                            </h2>
                            <Link
                                :href="currentBgVideo.slug"
                                class="mt-4 inline-flex min-h-9 items-center justify-between gap-2 border-t border-white/30 pt-3 text-left text-xs font-semibold text-white transition hover:text-[#A8E6EB] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white cursor-pointer"
                            >
                                <span class="inline-flex items-center gap-1.5">
                                    {{ t('Lihat destinasi', 'View destination') }}
                                </span>
                                <ArrowRight class="h-4 w-4 shrink-0" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Navigasi video dengan progres durasi tayang -->
                <div class="border-t border-white/25 pt-5 text-sm">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <button
                            type="button"
                            @click="toggleBgVideoPlayback"
                            :aria-label="isBgVideoPaused ? t('Putar video latar', 'Play background video') : t('Jeda video latar', 'Pause background video')"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/40 text-white transition hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white cursor-pointer"
                        >
                            <Play v-if="isBgVideoPaused" class="h-3.5 w-3.5" />
                            <Pause v-else class="h-3.5 w-3.5" />
                        </button>
                        <div class="flex min-w-0 gap-3 overflow-x-auto pb-1 sm:gap-4">
                        <button
                            v-for="(vItem, vIdx) in heroShowcases"
                            :key="'vid-' + vItem.id"
                            type="button"
                            @click="selectBgVideo(vIdx)"
                            :aria-pressed="activeVideoIdx === vIdx"
                            class="group relative w-[144px] shrink-0 rounded-lg px-3 pt-2 pb-3 text-left transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white cursor-pointer sm:w-[164px]"
                            :class="activeVideoIdx === vIdx ? 'bg-white/12 text-white' : 'text-white/75 hover:bg-white/5 hover:text-white'"
                        >
                            <span class="flex items-center gap-2 font-semibold"><span class="text-[10px] font-medium tabular-nums text-white/65">{{ String(vIdx + 1).padStart(2, '0') }}</span>{{ vItem.labelPendek }}</span>
                            <span class="mt-1 block pl-5 text-xs text-white/75">{{ vItem.wilayah }}</span>
                            <span
                                class="absolute bottom-0 left-0 h-0.5 w-full overflow-hidden rounded-full bg-white/25"
                            >
                                <span
                                    v-if="activeVideoIdx === vIdx"
                                    :ref="setVideoProgressRef"
                                    class="block h-full w-full origin-left bg-[#7DE2E8]"
                                    style="transform: scaleX(0)"
                                ></span>
                            </span>
                        </button>
                        </div>
                    </div>


                </div>
            </div>
        </section>

        <!-- SECTION 2: DESTINASI UNGGULAN -->
        <section class="mx-auto w-full max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-tinta sm:text-3xl">
                        {{ t('section_unggulan') }}
                    </h2>
                    <p class="mt-1.5 max-w-[60ch] text-sm text-tinta-redup">
                        {{ t('Dipilih dari jumlah kunjungan, pertumbuhan, dan rating ulasan wisatawan.', 'Selected by visitor volume, growth rate, and verified review ratings.') }}
                    </p>
                </div>
                <Link
                    href="/destinasi"
                    class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-utama-teks hover:underline underline-offset-4 cursor-pointer"
                >
                    <span>{{ t('Lihat semua destinasi', 'View all destinations') }}</span>
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <div class="mb-6 flex gap-2 overflow-x-auto pb-1 gulir-tipis" role="tablist" :aria-label="t('Filter kategori', 'Filter category')">
                <button
                    v-for="tabKat in ['SEMUA', 'Bahari', 'Alam', 'Budaya & Sejarah', 'Ikon Kota']"
                    :key="tabKat"
                    type="button"
                    role="tab"
                    :aria-selected="activeUnggulanFilter === tabKat"
                    @click="activeUnggulanFilter = tabKat"
                    :class="[
                        'shrink-0 rounded-lg border px-3.5 py-1.5 text-sm font-medium transition cursor-pointer',
                        activeUnggulanFilter === tabKat
                            ? 'border-utama bg-utama text-white'
                            : 'border-garis bg-permukaan text-tinta-redup hover:border-garis-kuat hover:text-tinta',
                    ]"
                >
                    {{ tabKat === 'SEMUA' ? t('Semua', 'All') : tabKat }}
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <KartuDestinasi
                    v-for="dest in filteredUnggulan"
                    :key="dest.id + '-' + activeUnggulanFilter"
                    :destinasi="dest"
                    :is-wishlisted="userWishlistIds.includes(dest.id)"
                    class="muncul-halus"
                />
            </div>
        </section>

        <!-- SECTION 3: JELAJAHI 13 KABUPATEN/KOTA SULAWESI TENGAH -->
        <section class="border-y border-garis bg-permukaan-redup px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-5">
                <div>
                    <h2 class="font-heading text-2xl font-bold text-tinta">{{ t('Jelajahi 27 Destinasi di 13 Kabupaten/Kota', 'Explore 27 Destinations Across 13 Regions') }}</h2>
                    <p class="mt-1 text-sm text-tinta-redup">{{ t('Mulai dari keindahan bawah laut Kepulauan Togean, cagar biosfer Lore Lindu, hingga pesona Danau Poso.', 'From the underwater wonders of Togean, Lore Lindu biosphere, to exotic Lake Poso.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/peta"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0E7C86] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0A5F67] hover:shadow whitespace-nowrap min-h-[42px]"
                    >
                        <span>{{ t('Buka Peta Wisata Interaktif', 'Open Interactive Map') }}</span>
                    </Link>
                    <Link
                        href="/itinerari"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-garis bg-white px-5 py-2.5 text-xs font-bold text-tinta shadow-sm transition hover:bg-utama-muda/50 whitespace-nowrap min-h-[42px]"
                    >
                        <span>{{ t('Perencana Perjalanan', 'Trip Planner') }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- SECTION 4: EVENT TERDEKAT & BERITA DINAS -->
        <section class="mx-auto w-full max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <!-- Event terdekat: satu sorotan berfoto + daftar bertanggal -->
                <div class="lg:col-span-7">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <h2 class="font-heading flex items-center gap-2 text-2xl font-bold text-tinta">
                            <Calendar class="h-5 w-5 text-utama" />
                            {{ t('section_event') }}
                        </h2>
                        <Link
                            href="/informasi"
                            class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-utama-teks hover:underline underline-offset-4 cursor-pointer"
                        >
                            <span>{{ t('Kalender event', 'Event calendar') }}</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <article
                        v-if="eventTerdekat.length > 0"
                        @click="openEventDetail(eventTerdekat[0])"
                        class="group relative mb-2 block overflow-hidden rounded-2xl bg-navy cursor-pointer"
                    >
                        <img
                            :src="resolveEventPhoto(eventTerdekat[0], 0)"
                            :alt="eventTerdekat[0].nama_event_id"
                            loading="lazy"
                            class="h-60 w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B1F2E]/90 via-[#0B1F2E]/30 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 flex items-end gap-4 p-5 text-white">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-white text-tinta">
                                <span class="font-heading text-xl font-bold leading-none tabular-nums">
                                    {{ parseDateParts(eventTerdekat[0].tanggal_mulai).day }}
                                </span>
                                <span class="mt-0.5 text-[11px] font-semibold text-utama-teks">
                                    {{ parseDateParts(eventTerdekat[0].tanggal_mulai).monthShort }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-white/80">
                                    {{ eventTerdekat[0].wilayah?.nama_wilayah }} · {{ getEventDurationDays(eventTerdekat[0].tanggal_mulai, eventTerdekat[0].tanggal_selesai) }} {{ t('hari', 'days') }} · {{ formatCurrency(eventTerdekat[0].harga_tiket_rp) }}
                                </p>
                                <h3 class="font-heading mt-0.5 text-xl font-bold leading-snug">
                                    {{ locale === 'en' ? eventTerdekat[0].nama_event_en : eventTerdekat[0].nama_event_id }}
                                </h3>
                            </div>
                        </div>
                    </article>

                    <ul class="divide-y divide-garis">
                        <li v-for="ev in eventTerdekat.slice(1)" :key="ev.id">
                            <button
                                type="button"
                                @click="openEventDetail(ev)"
                                class="group flex w-full items-center gap-4 py-4 text-left cursor-pointer"
                            >
                                <div class="w-12 shrink-0 text-center">
                                    <div class="font-heading text-xl font-bold leading-none text-tinta tabular-nums">
                                        {{ parseDateParts(ev.tanggal_mulai).day }}
                                    </div>
                                    <div class="mt-1 text-[11px] font-semibold text-utama-teks">
                                        {{ parseDateParts(ev.tanggal_mulai).monthShort }}
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-tinta group-hover:text-utama-teks">
                                        {{ locale === 'en' ? ev.nama_event_en : ev.nama_event_id }}
                                    </h3>
                                    <p class="mt-0.5 truncate text-sm text-tinta-redup">
                                        {{ ev.kategori_event }} · {{ ev.wilayah?.nama_wilayah }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-sm font-semibold tabular-nums"
                                    :class="Number(ev.harga_tiket_rp) === 0 ? 'text-sukses' : 'text-tinta'"
                                >
                                    {{ formatCurrency(ev.harga_tiket_rp) }}
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Berita dinas: daftar dengan thumbnail kecil -->
                <div class="lg:col-span-5">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <h2 class="font-heading flex items-center gap-2 text-2xl font-bold text-tinta">
                            <Newspaper class="h-5 w-5 text-utama" />
                            {{ t('section_berita') }}
                        </h2>
                        <Link
                            href="/informasi#berita"
                            class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-utama-teks hover:underline underline-offset-4 cursor-pointer"
                        >
                            <span>{{ t('Semua berita', 'All news') }}</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <ul class="space-y-5">
                        <li v-for="(br, bIdx) in beritaTerbaru" :key="br.id">
                            <button
                                type="button"
                                @click="openBeritaDetail(br)"
                                class="group flex w-full gap-4 text-left cursor-pointer"
                            >
                                <img
                                    :src="resolveBeritaPhoto(br, bIdx)"
                                    :alt="br.judul_id"
                                    loading="lazy"
                                    class="h-20 w-28 shrink-0 rounded-xl object-cover"
                                />
                                <div class="min-w-0">
                                    <p class="text-xs text-tinta-redup">
                                        {{ br.kategori_berita }} · {{ parseDateParts(br.tanggal_terbit).full }}
                                    </p>
                                    <h3 class="mt-1 line-clamp-2 font-semibold leading-snug text-tinta group-hover:text-utama-teks">
                                        {{ locale === 'en' ? br.judul_en : br.judul_id }}
                                    </h3>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- =============================================================== -->
        <!-- MODAL DETAIL CEPAT EVENT ATAU ARTIKEL BERITA (INTERAKTIF)       -->
        <!-- =============================================================== -->
        <Teleport to="body">
            <div
                v-if="activeEventModal || activeBeritaModal"
                class="fixed inset-0 z-[80] flex items-center justify-center p-4"
            >
                <div
                    class="fixed inset-0 bg-black/60 backdrop-blur-xs"
                    @click="activeEventModal = null; activeBeritaModal = null"
                ></div>

                <!-- Modal Event -->
                <div
                    v-if="activeEventModal"
                    class="relative z-10 w-full max-w-xl overflow-hidden rounded-2xl border border-garis bg-permukaan text-tinta shadow-2xl muncul-halus"
                >
                    <div class="relative h-52 w-full bg-navy">
                        <img
                            :src="resolveEventPhoto(activeEventModal, 0)"
                            :alt="activeEventModal.nama_event_id"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#12344D] via-[#12344D]/35 to-transparent"></div>
                        <button
                            type="button"
                            @click="activeEventModal = null"
                            class="absolute top-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition cursor-pointer"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <div class="absolute bottom-4 left-5 right-5 text-white">
                            <span class="inline-block rounded-lg bg-[#0E7C86] px-2.5 py-0.5 text-[11px] font-extrabold">
                                {{ activeEventModal.kategori_event }} • {{ activeEventModal.wilayah?.nama_wilayah }}
                            </span>
                            <h3 class="font-heading mt-1.5 text-xl font-extrabold">
                                {{ locale === 'en' ? activeEventModal.nama_event_en : activeEventModal.nama_event_id }}
                            </h3>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-permukaan-redup p-3.5 text-xs">
                            <div>
                                <span class="text-tinta-redup block">Jadwal Pelaksanaan</span>
                                <strong class="font-mono text-tinta">
                                    {{ parseDateParts(activeEventModal.tanggal_mulai).full }} – {{ parseDateParts(activeEventModal.tanggal_selesai).full }}
                                </strong>
                            </div>
                            <div>
                                <span class="text-tinta-redup block">Tiket Masuk Agenda</span>
                                <strong class="font-mono text-utama-teks">
                                    {{ formatCurrency(activeEventModal.harga_tiket_rp) }}
                                </strong>
                            </div>
                            <div class="col-span-2">
                                <span class="text-tinta-redup block">Lokasi Penyelenggaraan</span>
                                <strong class="text-tinta">{{ activeEventModal.lokasi_detail }}</strong>
                            </div>
                        </div>

                        <p class="text-xs sm:text-sm leading-relaxed text-tinta-redup">
                            {{ activeEventModal.deskripsi_id || 'Agenda pariwisata dan budaya resmi Provinsi Sulawesi Tengah yang diselenggarakan bersama Pemerintah Kabupaten/Kota dan pelaku ekonomi kreatif daerah.' }}
                        </p>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-garis">
                            <button
                                type="button"
                                @click="activeEventModal = null"
                                class="rounded-xl border border-garis px-4 py-2 text-xs font-bold text-tinta-redup hover:text-tinta cursor-pointer"
                            >
                                Tutup
                            </button>
                            <Link
                                href="/informasi"
                                class="tombol-utama inline-flex items-center gap-1.5 px-4 text-xs"
                            >
                                <span>Buka Halaman Kalender Event</span>
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Modal Berita -->
                <div
                    v-if="activeBeritaModal"
                    class="relative z-10 w-full max-w-xl overflow-hidden rounded-2xl border border-garis bg-permukaan text-tinta shadow-2xl muncul-halus"
                >
                    <div class="relative h-52 w-full bg-navy">
                        <img
                            :src="resolveBeritaPhoto(activeBeritaModal, 0)"
                            :alt="activeBeritaModal.judul_id"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#12344D] via-[#12344D]/35 to-transparent"></div>
                        <button
                            type="button"
                            @click="activeBeritaModal = null"
                            class="absolute top-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition cursor-pointer"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <div class="absolute bottom-4 left-5 right-5 text-white">
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="rounded-lg bg-[#0E7C86] px-2.5 py-0.5 font-extrabold">
                                    {{ activeBeritaModal.kategori_berita }}
                                </span>
                                <span class="font-mono text-[#D5EEF0]">
                                    {{ parseDateParts(activeBeritaModal.tanggal_terbit).full }}
                                </span>
                            </div>
                            <h3 class="font-heading mt-1.5 text-lg sm:text-xl font-extrabold leading-snug">
                                {{ locale === 'en' ? activeBeritaModal.judul_en : activeBeritaModal.judul_id }}
                            </h3>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <p class="text-xs sm:text-sm font-semibold leading-relaxed text-tinta">
                            {{ locale === 'en' ? activeBeritaModal.ringkasan_en : activeBeritaModal.ringkasan_id }}
                        </p>
                        <p class="text-xs sm:text-sm leading-relaxed text-tinta-redup">
                            {{ activeBeritaModal.isi_id || activeBeritaModal.ringkasan_id }}
                        </p>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-garis">
                            <button
                                type="button"
                                @click="activeBeritaModal = null"
                                class="rounded-xl border border-garis px-4 py-2 text-xs font-bold text-tinta-redup hover:text-tinta cursor-pointer"
                            >
                                Tutup
                            </button>
                            <Link
                                href="/informasi"
                                class="tombol-utama inline-flex items-center gap-1.5 px-4 text-xs"
                            >
                                <span>Semua Artikel & Pengaduan</span>
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

