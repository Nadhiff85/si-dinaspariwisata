<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import PhotoCredit from '@/Components/destinasi/PhotoCredit.vue';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PetaChoropleth from '@/Components/chart/PetaChoropleth.vue';
import { useAppContext } from '@/composables/useAppContext';
import { MapPin, Star, Clock, ArrowUpRight, Search } from 'lucide-vue-next';

const props = defineProps({
    destinasiList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    kategoriList: { type: Array, default: () => [] },
});

const { t, formatCurrency } = useAppContext();
const activeKategori = ref('SEMUA');
const activeWilayah = ref('');
const searchText = ref('');
const selectedDest = ref(props.destinasiList[0] || null);

const filteredDestinasi = computed(() => {
    return props.destinasiList.filter((d) => {
        if (activeKategori.value !== 'SEMUA' && d.kategori !== activeKategori.value) return false;
        if (activeWilayah.value && Number(d.wilayah_id) !== Number(activeWilayah.value)) return false;
        if (searchText.value && !d.nama_destinasi.toLowerCase().includes(searchText.value.toLowerCase())) return false;
        return true;
    });
});

watch(filteredDestinasi, (destinations) => {
    if (!destinations.some((destination) => destination.id === selectedDest.value?.id)) {
        selectedDest.value = destinations[0] || null;
    }
});
</script>

<template>
    <AppLayout>
        <Head :title="t('Peta Wisata Interaktif Sulawesi Tengah', 'Central Sulawesi tourism map')" />

        <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
            <PageHeading :title="t(&quot;Peta Geospasial Wisata Sulawesi Tengah&quot;, &quot;Find your next destination&quot;)" :section="t('Peta wisata', 'Tourism map')" :description="t('Temukan destinasi di peta. Pilih penanda untuk melihat lokasi dan informasi kunjungan.', 'Find destinations on the map. Select a marker for locations and visitor information.')" />
<div class="page-toolbar"><div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="activeKategori = 'SEMUA'"
                        :class="[
                            'rounded-xl px-3 py-1.5 text-xs font-bold transition',
                            activeKategori === 'SEMUA' ? 'bg-[#0E7C86] text-white' : 'bg-[var(--warna-permukaan)] border border-[var(--warna-garis)]',
                        ]"
                    > {{ t("Semua (", "All (") }}{{ destinasiList.length }})
                    </button>
                    <button
                        v-for="kat in kategoriList"
                        :key="kat"
                        type="button"
                        @click="activeKategori = kat"
                        :class="[
                            'rounded-xl px-3 py-1.5 text-xs font-bold transition',
                            activeKategori === kat ? 'bg-[#0E7C86] text-white' : 'bg-[var(--warna-permukaan)] border border-[var(--warna-garis)]',
                        ]"
                    >
                        {{ kat }}
                    </button>
                </div></div>

            <!-- Tata Letak Peta sesuai Wireframe DESIGN.md Bagian 7 -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Daftar Samping Kiri -->
                <div class="kartu-togean order-2 flex h-[400px] flex-col p-4 lg:order-1 lg:col-span-4 lg:h-[580px]">
                    <div class="space-y-2 border-b border-[var(--warna-garis)] pb-3">
                        <div class="relative">
                            <Search class="absolute top-3 left-3 h-4 w-4 text-[var(--warna-teks-redup)]" />
                            <input
                                :aria-label="t('Cari destinasi di peta', 'Search map destinations')"
                                v-model="searchText"
                                type="text"
                                :placeholder="t('Cari nama destinasi di peta...', 'Search map destinations?')"
                                class="h-9 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] pr-3 pl-9 text-xs"
                            />
                        </div>
                        <select
                            :aria-label="t('Wilayah destinasi', 'Destination region')"
                            v-model="activeWilayah"
                            class="h-9 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-2.5 text-xs font-semibold"
                        >
                            <option value="">{{ t("Semua Kabupaten / Kota (13)", "All 13 regions / cities") }}</option>
                            <option v-for="w in wilayahList" :key="w.id" :value="w.id">{{ w.nama_wilayah }}</option>
                        </select>
                    </div>

                    <div class="mt-3 min-h-0 flex-1 space-y-2.5 overflow-y-auto pr-1">
                        <p v-if="!filteredDestinasi.length" class="p-4 text-sm text-tinta-redup">{{ t('Tidak ada destinasi yang sesuai. Coba ubah filter.', 'No destinations match. Try changing the filters.') }}</p>
                        <button
                            v-for="d in filteredDestinasi"
                            :key="d.id"
                            type="button"
                            @click="selectedDest = d"
                            :class="[
                                'flex w-full items-start gap-3 rounded-xl border p-2.5 text-left transition',
                                selectedDest?.id === d.id
                                    ? 'border-[#0E7C86] bg-[#D5EEF0]/50'
                                    : 'border-[var(--warna-garis)] bg-[var(--warna-pasir)]/50 hover:border-[#0E7C86]',
                            ]"
                        >
                            <img :src="d.foto_utama" :alt="d.nama_destinasi" class="h-14 w-16 shrink-0 rounded-lg object-cover" />
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-bold text-[#0E7C86] uppercase">{{ d.kategori }}</span>
                                <h3 class="truncate font-heading text-xs font-bold text-[var(--warna-teks)]">{{ d.nama_destinasi }}</h3>
                                <p class="truncate text-[11px] text-[var(--warna-teks-redup)]">{{ d.wilayah?.nama_wilayah }}</p>
                                <div class="mt-1 flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-[#0A5F67] tabular-nums">{{ formatCurrency(d.harga_tiket_dewasa) }}</span>
                                    <span class="font-bold text-[#E0A422]">★ {{ d.rating_rata_rata }}</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Peta Interaktif Kanan + Kartu Info Terpilih -->
                <div class="order-1 space-y-4 lg:order-2 lg:col-span-8">
                    <div class="kartu-togean p-3">
                        <PetaChoropleth
                            :destinasi-data="filteredDestinasi"
                            mode="destinasi"
                            height="440px"
                            @select-item="(item) => (selectedDest = item)"
                        />
                    </div>

                    <div v-if="selectedDest" class="kartu-togean flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-4">
                            <img :src="selectedDest.foto_utama" :alt="selectedDest.nama_destinasi" class="h-18 w-24 rounded-xl object-cover" />
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-[#D5EEF0] px-2 py-0.5 text-[11px] font-bold text-[#0A5F67]">
                                        {{ selectedDest.kategori }}
                                    </span>
                                    <span class="text-xs text-[var(--warna-teks-redup)]">
                                        GPS: {{ selectedDest.latitude }}, {{ selectedDest.longitude }}
                                    </span>
                                </div>
                                <h3 class="font-heading mt-1 text-lg font-bold text-[var(--warna-teks)]">
                                    {{ selectedDest.nama_destinasi }}
                                </h3>
                                <PhotoCredit :photo="selectedDest.foto_attribution" class="text-tinta-redup" />
                                <p class="text-xs text-[var(--warna-teks-redup)]">
                                    {{ selectedDest.wilayah?.nama_wilayah }} • Jam Buka: {{ selectedDest.jam_operasional }} • Keramaian: {{ selectedDest.tingkat_keramaian_hari_ini }}
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="`/destinasi/${selectedDest.slug}`"
                            class="tombol-utama inline-flex shrink-0 items-center gap-1.5 px-4 text-xs"
                        >
                            <span>{{ t("Buka Halaman Destinasi", "View destination") }}</span>
                            <ArrowUpRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

