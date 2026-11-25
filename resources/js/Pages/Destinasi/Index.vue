<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAppContext } from '@/composables/useAppContext';
import KartuDestinasi from '@/Components/destinasi/KartuDestinasi.vue';
import PetaChoropleth from '@/Components/chart/PetaChoropleth.vue';
import { Search, SlidersHorizontal, LayoutGrid, Map as MapIcon, RotateCcw } from 'lucide-vue-next';

const { t, locale, formatNumber } = useAppContext();

const props = defineProps({
    destinasiList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    kategoriList: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    userWishlistIds: { type: Array, default: () => [] },
});

const viewMode = ref('grid');
const filtersOpen = ref(false);

const filterForm = reactive({
    q: props.filters.q || '',
    kategori: props.filters.kategori || 'SEMUA',
    wilayah_id: props.filters.wilayah_id || '',
    rating_min: props.filters.rating_min || '',
    harga_max: props.filters.harga_max || '',
    keramaian: props.filters.keramaian || 'SEMUA',
    urutkan: props.filters.urutkan || 'unggulan',
});

const applyFilters = () => {
    router.get('/destinasi', filterForm, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
    filterForm.q = '';
    filterForm.kategori = 'SEMUA';
    filterForm.wilayah_id = '';
    filterForm.rating_min = '';
    filterForm.harga_max = '';
    filterForm.keramaian = 'SEMUA';
    filterForm.urutkan = 'unggulan';
    applyFilters();
};
</script>

<template>
    <AppLayout>
        <Head :title="t('Jelajah 27 Destinasi Sulawesi Tengah', 'Explore Central Sulawesi destinations')" />

        <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header Halaman -->
            <PageHeading :title="t(&quot;Jelajah Destinasi Sulawesi Tengah&quot;, &quot;Explore Central Sulawesi&quot;)" :section="t('Destinasi', 'Destinations')">
<template #summary><p class="mt-1 text-sm text-[var(--warna-teks-redup)]"> {{ t("Menampilkan", "Showing") }} <strong class="font-bold text-[#0E7C86] tabular-nums">{{ destinasiList.length }}</strong> {{ t("destinasi berdasarkan filter aktif Anda.", "destinations matching your filters.") }} </p></template>
<template #actions><div class="flex flex-wrap items-center gap-2.5">
                    <select
                        v-model="filterForm.urutkan"
                        @change="applyFilters"
                        :aria-label="t('Urutkan Destinasi', 'Sort destinations')"
                        class="h-10 rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-permukaan)] px-3 text-xs font-semibold text-[var(--warna-teks)]"
                    >
                        <option value="unggulan">{{ t("Urutkan: Unggulan Dinas", "Featured destinations") }}</option>
                        <option value="rating_tertinggi">{{ t("Urutkan: Rating Tertinggi", "Highest rated") }}</option>
                        <option value="harga_terendah">{{ t("Urutkan: Tiket Termurah", "Lowest ticket price") }}</option>
                        <option value="harga_tertinggi">{{ t("Urutkan: Tiket Tertinggi", "Highest ticket price") }}</option>
                        <option value="paling_sepi">{{ t("Urutkan: Paling Sepi Hari Ini", "Least crowded") }}</option>
                        <option value="nama_az">Urutkan: Nama A–Z</option>
                    </select>

                    <div class="inline-flex rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-permukaan)] p-1">
                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                                viewMode === 'grid' ? 'bg-[#0E7C86] text-white' : 'text-[var(--warna-teks-redup)]',
                            ]"
                        >
                            <LayoutGrid class="h-3.5 w-3.5" />
                            <span>{{ t("Grid", "Grid") }}</span>
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'peta'"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition',
                                viewMode === 'peta' ? 'bg-[#0E7C86] text-white' : 'text-[var(--warna-teks-redup)]',
                            ]"
                        >
                            <MapIcon class="h-3.5 w-3.5" />
                            <span>{{ t("Peta", "Map") }}</span>
                        </button>
                    </div>
                </div></template>
</PageHeading>

            <!-- Tata Letak Sidebar Filter Kiri + Daftar Kanan (Wireframe DESIGN.md) -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <aside class="min-w-0 lg:col-span-3">
                    <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen" aria-controls="destination-filters" class="mb-3 flex min-h-12 w-full items-center justify-between rounded-xl border border-garis bg-permukaan px-4 text-sm font-semibold lg:hidden"><span>{{ t('Filter pencarian', 'Search filters') }}</span><SlidersHorizontal class="h-4 w-4" /></button>
                    <form id="destination-filters" @submit.prevent="applyFilters" :class="filtersOpen ? 'block' : 'hidden lg:block'" class="kartu-togean sticky top-22 space-y-4 p-5">
                        <div class="flex items-center justify-between border-b border-[var(--warna-garis)] pb-3">
                            <span class="font-heading flex items-center gap-2 text-sm font-bold">
                                <SlidersHorizontal class="h-4 w-4 text-[#0E7C86]" /> {{ t("Filter Pencarian", "Search filters") }} </span>
                            <button
                                type="button"
                                @click="resetFilters"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-[#C2492E] hover:underline"
                            >
                                <RotateCcw class="h-3 w-3" /> {{ t("Reset", "Reset") }} </button>
                        </div>

                        <div>
                            <label for="field-destinasi-index-filterForm-q" class="mb-1 block text-xs font-bold">{{ t("Kata Kunci", "Search term") }}</label>
                            <div class="relative">
                                <Search class="absolute top-3 left-3 h-4 w-4 text-[var(--warna-teks-redup)]" />
                                <input id="field-destinasi-index-filterForm-q"
                                    v-model="filterForm.q"
                                    type="text"
                                    placeholder="Togean, Poso, Talise..."
                                    class="h-10 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] pr-3 pl-9 text-xs"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold">{{ t("Kategori Wisata", "Tourism category") }}</label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="radio" v-model="filterForm.kategori" value="SEMUA" @change="applyFilters" class="accent-[#0E7C86]" />
                                    <span>{{ t("Semua Kategori", "All categories") }}</span>
                                </label>
                                <label v-for="kat in kategoriList" :key="kat" class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="radio" v-model="filterForm.kategori" :value="kat" @change="applyFilters" class="accent-[#0E7C86]" />
                                    <span>{{ kat }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="field-destinasi-index-filterForm-wilayah_id" class="mb-1 block text-xs font-bold">{{ t("Kabupaten / Kota", "Region / city") }}</label>
                            <select id="field-destinasi-index-filterForm-wilayah_id"
                                v-model="filterForm.wilayah_id"
                                @change="applyFilters"
                                class="h-10 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-2.5 text-xs"
                            >
                                <option value="">{{ t("Seluruh Sulawesi Tengah (13)", "All 13 regions") }}</option>
                                <option v-for="w in wilayahList" :key="w.id" :value="w.id">{{ w.nama_wilayah }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="field-destinasi-index-filterForm-harga_max" class="mb-1 block text-xs font-bold">{{ t("Batas Harga Tiket Dewasa", "Adult ticket budget") }}</label>
                            <select id="field-destinasi-index-filterForm-harga_max"
                                v-model="filterForm.harga_max"
                                @change="applyFilters"
                                class="h-10 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-2.5 text-xs"
                            >
                                <option value="">{{ t("Semua Rentang Harga", "Any price") }}</option>
                                <option value="10000">{{ t("Maksimal Rp 10.000", "Up to IDR 10,000") }}</option>
                                <option value="15000">{{ t("Maksimal Rp 15.000", "Up to IDR 15,000") }}</option>
                                <option value="20000">{{ t("Maksimal Rp 20.000", "Up to IDR 20,000") }}</option>
                                <option value="30000">{{ t("Maksimal Rp 30.000", "Up to IDR 30,000") }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="field-destinasi-index-filterForm-rating_min" class="mb-1 block text-xs font-bold">{{ t("Rating Minimum", "Minimum rating") }}</label>
                            <select id="field-destinasi-index-filterForm-rating_min"
                                v-model="filterForm.rating_min"
                                @change="applyFilters"
                                class="h-10 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-2.5 text-xs"
                            >
                                <option value="">{{ t("Semua Rating", "Any rating") }}</option>
                                <option value="4.5">★ 4.5 ke atas</option>
                                <option value="4.2">★ 4.2 ke atas</option>
                                <option value="4.0">★ 4.0 ke atas</option>
                            </select>
                        </div>

                        <div>
                            <label for="field-destinasi-index-filterForm-keramaian" class="mb-1 block text-xs font-bold">{{ t("Indikator Keramaian Hari Ini", "Crowd level") }}</label>
                            <select id="field-destinasi-index-filterForm-keramaian"
                                v-model="filterForm.keramaian"
                                @change="applyFilters"
                                class="h-10 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-2.5 text-xs"
                            >
                                <option value="SEMUA">{{ t("Semua Kondisi", "Any crowd level") }}</option>
                                <option value="Sepi">{{ t("Sepi (< 40%)", "Quiet (< 40%)") }}</option>
                                <option value="Sedang">Sedang (40% – 75%)</option>
                                <option value="Ramai">{{ t("Ramai (> 75%)", "Busy (> 75%)") }}</option>
                            </select>
                        </div>

                        <button type="submit" class="tombol-utama w-full text-xs"> {{ t("Terapkan Filter", "Apply filters") }} </button>
                    </form>
                </aside>

                <!-- Hasil Destinasi -->
                <div class="lg:col-span-9">
                    <template v-if="viewMode === 'grid'">
                        <div v-if="destinasiList.length" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            <KartuDestinasi catalog-view
                                v-for="dest in destinasiList"
                                :key="dest.id"
                                :destinasi="dest"
                                :is-wishlisted="userWishlistIds.includes(dest.id)"
                            />
                        </div>
                        <div v-else class="kartu-togean p-12 text-center">
                            <p class="font-heading text-lg font-bold text-[var(--warna-teks)]"> {{ t("Tidak ada destinasi yang cocok dengan filter tersebut", "No destinations match your filters") }} </p>
                            <p class="mt-1 text-xs text-[var(--warna-teks-redup)]"> {{ t("Coba perluas kriteria wilayah, kategori, atau batas harga tiket.", "Try a different region, category, or ticket budget.") }} </p>
                            <button type="button" @click="resetFilters" class="tombol-utama mt-4 px-5 text-xs"> {{ t("Tampilkan Semua Destinasi", "Show all destinations") }} </button>
                        </div>
                    </template>

                    <template v-else>
                        <div class="kartu-togean p-4">
                            <PetaChoropleth
                                :destinasi-data="destinasiList"
                                mode="destinasi"
                                height="560px"
                                @select-item="(d) => router.visit(`/destinasi/${d.slug}`)"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

