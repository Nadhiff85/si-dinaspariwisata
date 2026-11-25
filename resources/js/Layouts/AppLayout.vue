<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import {
    Home, Compass, Map, BarChart3, Calendar, Heart, Route as RouteIcon,
    Ticket, ShieldCheck, Building2, Sun, Moon, Menu, X, LogOut,
    CheckCircle2, AlertTriangle, Info, PanelLeftClose, PanelLeftOpen,
    Search, Sparkles, Database, Users, Layers
} from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';
import ChatbotWidget from '@/Components/ui/ChatbotWidget.vue';
import CommandPalette from '@/Components/ui/CommandPalette.vue';

const page = usePage();
const props = defineProps({ executiveView: { type: Object, default: null } });
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const currentUrl = computed(() => page.url || '/');
const { locale, theme, t, setLocale, toggleTheme } = useAppContext();

const mobileMenuOpen = ref(false);
const sidebarCollapsed = ref(false);
const dismissFlash = ref(false);
const commandPaletteOpen = ref(false);

function isActive(path) {
    if (path === '/') {
        return currentUrl.value === '/';
    }
    return currentUrl.value.startsWith(path);
}

function isExecutiveLinkActive(href) {
    if (!isOnAdminDashboard.value) return false;
    const query = new URL(href, 'http://localhost').searchParams;
    const current = new URL(currentUrl.value, 'http://localhost').searchParams;
    const tab = props.executiveView?.tab || current.get('tab') || 'ringkasan';
    return query.get('tab') === tab;
}

const isOnAdminDashboard = computed(() => currentUrl.value.startsWith('/admin/dashboard'));
const isDashboardLayout = computed(() =>
    currentUrl.value.startsWith('/admin') || currentUrl.value.startsWith('/pengelola')
);
const isPublicHome = computed(() => currentUrl.value.split('?')[0] === '/');
let flashTimer = null;
watch(
    () => [flash.value.success, flash.value.error, flash.value.info, currentUrl.value],
    ([s, e, i]) => {
        mobileMenuOpen.value = false;
        if (s || e || i) {
            dismissFlash.value = false;
            if (flashTimer) clearTimeout(flashTimer);
            flashTimer = setTimeout(() => {
                dismissFlash.value = true;
            }, 6000);
        }
    },
    { immediate: true }
);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen flex bg-latar text-tinta" :class="{ 'workspace-shell': !isPublicHome }">
        <!-- Overlay Gelap untuk Drawer Mobile -->
        <div
            v-if="mobileMenuOpen"
            @click="mobileMenuOpen = false"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs"
            :class="isDashboardLayout ? 'lg:hidden' : 'xl:hidden'"
        ></div>

        <!-- =============================================================== -->
        <!-- SIDEBAR NAVIGASI SAMPING KIRI (Khusus Dashboard & Drawer Mobile)-->
        <!-- Dihapus dari Beranda Publik Desktop agar tampilan Publik FULL   -->
        <!-- =============================================================== -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-garis bg-permukaan transition-all duration-300 ease-in-out shadow-xs',
                isDashboardLayout
                    ? [
                        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
                        mobileMenuOpen ? 'w-72 translate-x-0 shadow-2xl' : 'w-72 -translate-x-full lg:translate-x-0',
                    ]
                    : [
                        mobileMenuOpen ? 'w-72 translate-x-0 shadow-2xl' : 'w-72 -translate-x-full',
                    ],
            ]"
        >
            <!-- Kepala Sidebar: Logo & Identitas Dinas Pariwisata -->
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-garis px-4">
                <Link href="/" class="flex items-center gap-3 min-w-0">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white p-1 border border-garis shadow-xs overflow-hidden"
                    >
                        <img
                            src="/images/logo-sulteng.png"
                            alt="Logo Provinsi Sulawesi Tengah"
                            class="h-full w-full object-contain"
                        />
                    </div>
                    <div v-if="!sidebarCollapsed" class="min-w-0">
                        <span class="font-heading block truncate text-sm font-extrabold tracking-tight text-tinta leading-tight">
                            Jelajah Sulteng
                        </span>
                        <span class="block truncate text-[11px] font-medium text-tinta-redup">
                            Dinas Pariwisata Provinsi
                        </span>
                    </div>
                </Link>

                <!-- Tombol Tutup Drawer di Mobile -->
                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    :aria-label="t('Tutup navigasi', 'Close navigation')"
                    class="rounded-lg p-1.5 text-tinta-redup hover:bg-utama-muda cursor-pointer"
                    :class="isDashboardLayout ? 'lg:hidden' : 'xl:hidden'"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Daftar Menu Navigasi Samping -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 gulir-tipis">
                <!-- Kelompok 1: Panel Manajemen & Analitik (Ditampilkan di Puncak bila Login sebagai Admin / Pengelola) -->
                <div
                    v-if="user && (user.peran === 'pengelola_destinasi' || user.peran === 'admin')"
                >
                    <div
                        v-if="!sidebarCollapsed"
                        class="px-3 mb-2 text-[10.5px] font-bold uppercase tracking-wider text-tinta-samar"
                    >
                        {{ t('Pusat Komando & Manajemen', 'Command & Management') }}
                    </div>
                    <div class="space-y-1.5">
                        <Link
                            v-if="user.peran === 'admin'"
                            href="/admin/dashboard"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_admin')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold transition-all',
                                isActive('/admin/dashboard')
                                    ? 'bg-navy text-white shadow-xs'
                                    : 'bg-utama-muda/70 text-utama-teks hover:bg-navy hover:text-white',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <ShieldCheck class="h-4 w-4 shrink-0 text-hijau-laut" />
                            <span v-if="!sidebarCollapsed">{{ t('Dashboard Akun Utama', 'Executive Dashboard') }}</span>
                        </Link>

                        <Link
                            href="/pengelola/dashboard"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_pengelola')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold transition-all',
                                isActive('/pengelola/dashboard')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Building2 class="h-4 w-4 shrink-0 text-utama" :class="isActive('/pengelola/dashboard') ? 'text-white' : ''" />
                            <span v-if="!sidebarCollapsed">{{ t('Panel Operasional Objek', 'Spot Manager Panel') }}</span>
                        </Link>
                    </div>

                    <!-- Navigasi dashboard admin -->
                    <div
                        v-if="user.peran === 'admin' && !sidebarCollapsed"
                        class="mt-3 ml-2 border-l-2 border-garis pl-2.5 space-y-1"
                    >
                        <div class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-tinta-samar">
                            {{ t('Analitik pariwisata', 'Tourism analytics') }}
                        </div>
                        <Link
                            v-for="subItem in [
                                { label: t('Statistik Resmi', 'Official statistics'), href: '/admin/dashboard?tab=ringkasan', badge: '30' },
                                { label: t('Rekomendasi Kebijakan', 'Policy recommendations'), href: '/admin/dashboard?tab=rekomendasi', badge: t('Aksi', 'Action') },
                                { label: t('27 Objek & Persetujuan', 'Destinations & approvals'), href: '/admin/dashboard?tab=master', badge: '27' },
                                { label: t('Gudang Data & Audit', 'Data warehouse & audit'), href: '/admin/dashboard?tab=warehouse', badge: 'DW' },
                                { label: t('Pengguna & Moderasi', 'Users & moderation'), href: '/admin/dashboard?tab=moderasi', badge: 'Log' },
                            ]"
                            :key="subItem.href"
                            :href="subItem.href"
                            @click="mobileMenuOpen = false"
                            class="executive-nav-link flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-[11.5px] font-medium transition"
                            :aria-current="isExecutiveLinkActive(subItem.href) ? 'page' : undefined"
                            :class="isExecutiveLinkActive(subItem.href) ? 'bg-utama-muda text-utama-teks font-semibold' : 'text-tinta-redup hover:bg-utama-muda/50 hover:text-tinta'"
                        >
                            <span class="min-w-0 leading-snug">{{ subItem.label }}</span>
                            <span class="rounded bg-permukaan-redup border border-garis px-1.5 py-0.2 font-mono text-[10px] font-semibold text-tinta-redup">
                                {{ subItem.badge }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Kelompok 2: Menu Eksplorasi Publik -->
                <div :class="user && (user.peran === 'pengelola_destinasi' || user.peran === 'admin') ? 'border-t border-garis pt-4' : ''">
                    <div
                        v-if="!sidebarCollapsed"
                        class="px-3 mb-2 text-[10.5px] font-bold uppercase tracking-wider text-tinta-samar"
                    >
                        {{ t('Portal Wisatawan Publik', 'Public Tourism Portal') }}
                    </div>
                    <div class="space-y-1">
                        <Link
                            href="/"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_beranda')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_beranda') }}</span>
                        </Link>

                        <Link
                            href="/destinasi"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_jelajah')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/destinasi')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Compass class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_jelajah') }}</span>
                        </Link>

                        <Link
                            href="/peta"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_peta')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/peta')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Map class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_peta') }}</span>
                        </Link>

                        <Link
                            v-if="user?.peran === 'admin'"
                            href="/admin/dashboard?tab=ringkasan"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_statistik')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/admin/dashboard')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <BarChart3 class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_statistik') }}</span>
                        </Link>

                        <Link
                            href="/informasi"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_informasi')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/informasi')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Calendar class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_informasi') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Kelompok 3: Fitur Perjalanan Wisatawan (Wishlist, Itinerari, Tiket) -->
                <div v-if="user" class="border-t border-garis pt-4">
                    <div
                        v-if="!sidebarCollapsed"
                        class="px-3 mb-2 text-[10.5px] font-bold uppercase tracking-wider text-tinta-samar"
                    >
                        {{ t('Perjalanan Saya', 'My Trip Tools') }}
                    </div>
                    <div class="space-y-1">
                        <Link
                            href="/wishlist"
                            @click="mobileMenuOpen = false"
                            :title="t('nav_wishlist')"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/wishlist')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Heart class="h-4 w-4 shrink-0" :class="isActive('/wishlist') ? 'text-white' : 'text-karang-teks'" />
                            <span v-if="!sidebarCollapsed">{{ t('nav_wishlist') }}</span>
                        </Link>

                        <Link
                            href="/itinerari"
                            @click="mobileMenuOpen = false"
                            title="Itinerari"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/itinerari')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <RouteIcon class="h-4 w-4 shrink-0" :class="isActive('/itinerari') ? 'text-white' : 'text-utama'" />
                            <span v-if="!sidebarCollapsed">Itinerari</span>
                        </Link>

                        <Link
                            href="/tiket"
                            @click="mobileMenuOpen = false"
                            title="Tiket"
                            :class="[
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all',
                                isActive('/tiket')
                                    ? 'bg-utama text-white shadow-xs'
                                    : 'text-tinta hover:bg-utama-muda hover:text-utama-teks',
                                sidebarCollapsed ? 'justify-center' : '',
                            ]"
                        >
                            <Ticket class="h-4 w-4 shrink-0" :class="isActive('/tiket') ? 'text-white' : 'text-utama'" />
                            <span v-if="!sidebarCollapsed">Tiket</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Kaki Sidebar: Tombol Ciutkan / Lebarkan Sidebar (Desktop) -->
            <div class="hidden lg:flex items-center justify-between border-t border-garis p-3">
                <button
                    type="button"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-garis bg-permukaan-redup px-3 py-2 text-xs font-semibold text-tinta-redup hover:border-utama hover:text-tinta transition cursor-pointer"
                >
                    <PanelLeftOpen v-if="sidebarCollapsed" class="h-4 w-4" />
                    <PanelLeftClose v-else class="h-4 w-4" />
                    <span v-if="!sidebarCollapsed">{{ t('Ciutkan Sidebar', 'Collapse Sidebar') }}</span>
                </button>
            </div>
        </aside>

        <!-- =============================================================== -->
        <!-- AREA KONTEN UTAMA (Full-Width di Beranda Publik, Sidebar di Dashboard) -->
        <!-- =============================================================== -->
        <div
            :class="[
                'flex min-h-screen flex-1 flex-col min-w-0 w-full transition-all duration-300 ease-in-out',
                isDashboardLayout ? (sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64') : '',
            ]"
        >
            <!-- Bilah Header Atas -->
            <header
                class="sticky top-0 z-30 shrink-0 border-b border-garis bg-permukaan/95 backdrop-blur-md"
                :class="isDashboardLayout ? 'h-16' : 'h-[72px]'"
            >
                <div class="mx-auto flex h-full w-full items-center justify-between gap-2 px-4 sm:gap-3 sm:px-6 lg:px-8" :class="isDashboardLayout ? '' : 'max-w-[1440px]'">
                    <!-- Sisi Kiri Header: Brand Logo (di Beranda Publik), Navigasi Publik, & Pencarian Ctrl+K -->
                    <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-4">
                        <button
                            type="button"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            :aria-label="t('Buka navigasi', 'Open navigation')"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-garis bg-permukaan-redup sm:h-10 sm:w-10 cursor-pointer"
                            :class="isDashboardLayout ? 'lg:hidden' : 'xl:hidden'"
                        >
                            <Menu class="h-5 w-5" />
                        </button>

                        <!-- Logo Identitas di Header ketika berada di Beranda Publik (Full-Width Mode) -->
                        <Link
                            v-if="!isDashboardLayout"
                            href="/"
                            class="flex items-center gap-2.5 shrink-0"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white p-1 border border-garis shadow-xs overflow-hidden sm:h-10 sm:w-10"
                            >
                                <img
                                    src="/images/logo-sulteng.png"
                                    alt="Logo Provinsi Sulawesi Tengah"
                                    class="h-full w-full object-contain"
                                />
                            </div>
                            <div class="hidden sm:block">
                                <span class="font-heading block text-sm font-extrabold tracking-tight text-tinta leading-tight whitespace-nowrap">
                                    Jelajah Sulteng
                                </span>
                                <span class="block text-[10.5px] font-medium text-tinta-redup whitespace-nowrap">
                                    Dinas Pariwisata Provinsi
                                </span>
                            </div>
                        </Link>

                        <!-- Navigasi Horizontal Ringkas khusus Halaman Publik Desktop -->
                        <nav
                            v-if="!isDashboardLayout"
                            :aria-label="t('Navigasi utama', 'Main navigation')"
                            class="hidden xl:flex items-center gap-1 border-l border-garis pl-4"
                        >
                            <Link
                                v-for="pubNav in [
                                    { href: '/', label: t('Beranda', 'Home'), exact: true },
                                    { href: '/destinasi', label: t('Destinasi', 'Destinations') },
                                    { href: '/peta', label: t('Peta', 'Map') },
                                    ...(user?.peran === 'admin' ? [{ href: '/admin/dashboard?tab=ringkasan', label: t('Statistik Resmi', 'Official Statistics') }] : []),
                                    { href: '/informasi', label: t('Informasi', 'Information') },
                                ]"
                                :key="pubNav.href"
                                :href="pubNav.href"
                                :class="[
                                    'rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-colors',
                                    (pubNav.exact ? currentUrl === '/' : isActive(pubNav.href))
                                        ? 'bg-utama text-white shadow-xs'
                                        : 'text-tinta-redup hover:bg-utama-muda/60 hover:text-tinta',
                                ]"
                            >
                                {{ pubNav.label }}
                            </Link>
                        </nav>

                        <!-- Tombol Pencarian & Komando Cepat (Ctrl+K) -->
                        <button
                            type="button"
                            @click="commandPaletteOpen = true"
                            :aria-label="t('Buka pencarian', 'Open search')"
                            :title="t('Cari destinasi atau halaman (Ctrl+K)', 'Search destinations or pages (Ctrl+K)')"
                            :class="[
                                'items-center gap-2.5 rounded-xl border border-garis bg-permukaan-redup text-xs text-tinta-redup hover:border-utama hover:text-tinta transition cursor-pointer',
                                !isPublicHome ? 'workspace-search' : '',
                                isDashboardLayout ? 'flex flex-1 max-w-md justify-between py-2 px-3.5' : 'ml-auto hidden h-10 w-10 shrink-0 justify-center sm:flex',
                            ]"
                        >
                            <span class="flex items-center gap-2 truncate">
                                <Search class="h-3.5 w-3.5 shrink-0 text-utama" />
                                <span v-if="isDashboardLayout" class="truncate">{{ t('Cari destinasi, analitik, atau halaman...', 'Search destinations, analytics, or pages...') }}</span>
                            </span>
                            <span v-if="isDashboardLayout" class="hidden sm:inline-flex items-center gap-1 shrink-0">
                                <kbd class="kbd">Ctrl</kbd>
                                <kbd class="kbd">K</kbd>
                            </span>
                        </button>
                    </div>

                    <!-- Sisi Kanan Header: Kontrol Bahasa, Tema & Akun -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Pintasan Cepat ke Dashboard bila sedang Login di Beranda Publik -->
                        <template v-if="!isDashboardLayout && user">
                            <Link
                                v-if="user.peran === 'admin'"
                                href="/admin/dashboard"
                                class="hidden md:inline-flex items-center gap-1.5 rounded-xl bg-navy px-3 py-1.5 text-xs font-bold text-white hover:bg-utama transition whitespace-nowrap"
                            >
                                <ShieldCheck class="h-3.5 w-3.5 text-hijau-laut" />
                                <span>{{ t('Dashboard', 'Dashboard') }}</span>
                            </Link>
                            <Link
                                v-else-if="user.peran === 'pengelola_destinasi'"
                                href="/pengelola/dashboard"
                                class="hidden md:inline-flex items-center gap-1.5 rounded-xl bg-utama px-3 py-1.5 text-xs font-bold text-white hover:bg-navy transition whitespace-nowrap"
                            >
                                <Building2 class="h-3.5 w-3.5" />
                                <span>{{ t('Panel Pengelola', 'Manager Panel') }}</span>
                            </Link>
                        </template>

                        <!-- Pemilih Bahasa ID | EN -->
                        <div class="inline-flex items-center rounded-xl border border-garis bg-permukaan-redup p-0.5 text-xs font-bold">
                            <button
                                type="button"
                                @click="setLocale('id')"
                                :class="[
                                    'rounded-lg px-2.5 py-1 transition cursor-pointer',
                                    locale === 'id' ? 'bg-utama text-white' : 'text-tinta-redup',
                                ]"
                            >
                                ID
                            </button>
                            <button
                                type="button"
                                @click="setLocale('en')"
                                :class="[
                                    'rounded-lg px-2.5 py-1 transition cursor-pointer',
                                    locale === 'en' ? 'bg-utama text-white' : 'text-tinta-redup',
                                ]"
                            >
                                EN
                            </button>
                        </div>

                        <!-- Tombol Tema Terang / Gelap -->
                        <button
                            type="button"
                            @click="toggleTheme"
                            :aria-label="theme === 'gelap' ? t('Mode terang', 'Light mode') : t('Mode gelap', 'Dark mode')"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-garis bg-permukaan-redup text-tinta transition hover:border-utama cursor-pointer"
                        >
                            <Sun v-if="theme === 'gelap'" class="h-4 w-4 text-emas" />
                            <Moon v-else class="h-4 w-4 text-tinta" />
                        </button>

                        <!-- Status Login / Tombol Masuk -->
                        <template v-if="user">
                            <div class="flex items-center gap-2 rounded-xl border border-garis bg-permukaan-redup px-3 py-1.5">
                                <div class="text-right hidden 2xl:block">
                                    <div class="text-xs font-bold leading-tight text-tinta whitespace-nowrap">{{ user.name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-utama-teks whitespace-nowrap">
                                        {{ user.peran === 'admin' ? 'AKUN UTAMA (ADMIN)' : user.peran.replace('_', ' ') }}
                                    </div>
                                </div>
                                <Link
                                    href="/login"
                                    :aria-label="t('Ganti akun', 'Switch account')"
                                    title="Ganti Akun (Akun Utama / 27 Pengelola Wisata)"
                                    class="rounded-lg bg-utama-muda px-2.5 py-1 text-[11px] font-bold text-utama-teks hover:bg-utama hover:text-white transition whitespace-nowrap"
                                >
                                    <template v-if="isPublicHome">{{ t('Ganti Akun', 'Switch Account') }}</template>
                                    <template v-else><Users class="h-4 w-4 sm:hidden" /><span class="hidden sm:inline">{{ t('Ganti Akun', 'Switch Account') }}</span></template>
                                </Link>
                                <button
                                    type="button"
                                    @click="logout"
                                    title="Keluar"
                                    class="rounded-lg p-1 text-bahaya hover:bg-bahaya/10 cursor-pointer"
                                >
                                    <LogOut class="h-4 w-4" />
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="tombol-utama inline-flex items-center px-4 text-xs whitespace-nowrap"
                            >
                                <span class="sm:hidden">{{ t('Masuk', 'Sign In') }}</span>
                                <span class="hidden sm:inline">{{ t('Masuk Akun', 'Sign In') }}</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </header>

            <!-- Notifikasi Flash / Toast Mengambang (Floating Toast) -->
            <Transition
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-[-12px] opacity-0 sm:translate-y-0 sm:translate-x-4"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="(flash.success || flash.error || flash.info) && !dismissFlash"
                    role="status"
                    aria-live="polite"
                    class="fixed top-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-auto sm:max-w-md pointer-events-auto"
                >
                    <div
                        :class="[
                            'flex items-center justify-between gap-3.5 rounded-2xl border p-3.5 shadow-2xl backdrop-blur-md transition-all',
                            flash.success ? 'border-emerald-200 bg-white/95 text-tinta dark:bg-[#102C41]/95 dark:border-emerald-800' : '',
                            flash.error ? 'border-rose-200 bg-white/95 text-bahaya dark:bg-[#102C41]/95 dark:border-rose-800' : '',
                            flash.info ? 'border-utama/30 bg-white/95 text-tinta dark:bg-[#102C41]/95 dark:border-utama/40' : '',
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                :class="[
                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                                    flash.success ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : '',
                                    flash.error ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400' : '',
                                    flash.info ? 'bg-utama-muda text-utama dark:bg-utama/20 dark:text-utama-teks' : '',
                                ]"
                            >
                                <CheckCircle2 v-if="flash.success" class="h-5 w-5" />
                                <AlertTriangle v-else-if="flash.error" class="h-5 w-5" />
                                <Info v-else class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 pr-1">
                                <p class="text-xs font-bold leading-snug break-words">
                                    {{ flash.success || flash.error || flash.info }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="dismissFlash = true"
                            :aria-label="t('Tutup notifikasi', 'Close notification')"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-tinta-redup hover:bg-black/5 hover:text-tinta transition cursor-pointer dark:hover:bg-white/10"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Konten Utama Halaman -->
            <main class="min-w-0 flex-1" :class="!isPublicHome ? ['workspace-content', isDashboardLayout ? 'dashboard-content' : 'visitor-content'] : null">
                <slot />
            </main>

            <!-- Kaki Halaman Resmi & Pernyataan Transparansi Data -->
            <footer class="mt-16 border-t border-garis bg-navy text-[#F5EFE3]">
                <div class="mx-auto max-w-[1440px] px-4 py-10 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-12">
                        <div class="md:col-span-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1 shadow-sm overflow-hidden">
                                    <img
                                        src="/images/logo-sulteng.png"
                                        alt="Logo Provinsi Sulawesi Tengah"
                                        class="h-full w-full object-contain"
                                    />
                                </div>
                                <div>
                                    <div class="font-heading text-base font-bold text-white">Jelajah Sulteng</div>
                                    <div class="text-xs text-[#9DB2C0]">{{ isPublicHome ? 'Dinas Pariwisata Provinsi Sulawesi Tengah' : t('Dinas Pariwisata Provinsi Sulawesi Tengah', 'Central Sulawesi Tourism Department') }}</div>
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-relaxed text-[#9DB2C0]">
                                {{ t('disclaimer_data') }}
                            </p>
                        </div>

                        <div class="md:col-span-3">
                            <h4 class="font-heading text-xs font-bold uppercase tracking-wider text-white">{{ isPublicHome ? 'Eksplorasi & Layanan' : t('Eksplorasi & Layanan', 'Explore & services') }}</h4>
                            <ul class="mt-3 space-y-2 text-xs text-[#9DB2C0]">
                                <li><Link href="/destinasi" class="hover:text-white">{{ isPublicHome ? 'Katalog 27 Destinasi Unggulan' : t('Katalog 27 Destinasi Unggulan', 'Explore destinations') }}</Link></li>
                                <li><Link href="/peta" class="hover:text-white">{{ isPublicHome ? 'Peta Sebaran 13 Kabupaten/Kota' : t('Peta Sebaran 13 Kabupaten/Kota', 'Interactive regional map') }}</Link></li>
                                <li v-if="user?.peran === 'admin'"><Link href="/admin/dashboard?tab=ringkasan" class="hover:text-white">{{ isPublicHome ? 'Statistik Resmi Eksekutif' : t('Statistik Resmi Eksekutif', 'Executive tourism statistics') }}</Link></li>
                                <li><Link href="/itinerari" class="hover:text-white">{{ isPublicHome ? 'Perencana Itinerari & Estimasi Biaya' : t('Perencana Itinerari & Estimasi Biaya', 'Trip planner & budget') }}</Link></li>
                                <li><Link href="/informasi" class="hover:text-white">{{ isPublicHome ? 'Kalender Event & Kotak Pengaduan' : t('Kalender Event & Kotak Pengaduan', 'Events & visitor feedback') }}</Link></li>
                            </ul>
                        </div>

                        <div class="md:col-span-4">
                            <h4 class="font-heading text-xs font-bold uppercase tracking-wider text-white">{{ isPublicHome ? 'Dinas Pariwisata Provinsi Sulawesi Tengah' : t('Dinas Pariwisata Provinsi Sulawesi Tengah', 'Central Sulawesi Tourism Department') }}</h4>
                            <p class="mt-3 text-xs leading-relaxed text-[#9DB2C0]">
                                Jl. Dewi Sartika No. 45, Birobuli Utara, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah 94111<br />
                                Acuan Data: BPS Provinsi Sulawesi Tengah & Satu Data Sulteng.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-5 flex flex-col sm:flex-row items-center justify-between text-xs text-[#9DB2C0]">
                        <span>&copy; 2026 Dinas Pariwisata Provinsi Sulawesi Tengah.</span>
                        <span>Palet Desain Laut Togean • Ketik Ctrl+K untuk Navigasi Cepat</span>
                    </div>
                </div>
            </footer>

            <!-- Widget Chatbot Rekomendasi -->
            <ChatbotWidget />

            <!-- Modal Command Palette (Ctrl+K) -->
            <CommandPalette v-model:open="commandPaletteOpen" />
        </div>
    </div>
</template>
