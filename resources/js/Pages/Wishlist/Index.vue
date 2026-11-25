<template>
    <AppLayout>
        <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <PageHeading :title="t('Destinasi favorit Anda', 'Your saved destinations')" :section="t('Wishlist', 'Wishlist')" :description="t('Simpan tempat pilihan Anda untuk merencanakan rute dan memesan tiket.', 'Keep your favourite places ready for planning routes and booking tickets.')">
<template #actions><div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/itinerari"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-sm font-semibold transition-colors shadow-sm"
                    >
                        <CalendarRange class="w-4 h-4" />
                        {{ t('Susun Itinerari Perjalanan', 'Build Trip Itinerary') }}
                    </Link>
                    <Link
                        href="/destinasi"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-[#12344D] dark:text-[#F1F5F9] text-sm font-semibold hover:border-[#0E7C86] transition-colors"
                    >
                        <Compass class="w-4 h-4 text-[#0E7C86]" />
                        {{ t('Jelajahi Lagi', 'Explore More') }}
                    </Link>
                </div></template>
</PageHeading>

            <!-- Empty State -->
            <div
                v-if="!wishlists || wishlists.length === 0"
                class="surface-card p-12 text-center max-w-xl mx-auto my-8"
            >
                <div class="w-14 h-14 rounded-2xl bg-[#E6F4F5] dark:bg-[#152A3D] text-[#0E7C86] flex items-center justify-center mx-auto mb-4">
                    <Heart class="w-7 h-7" />
                </div>
                <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] mb-2">
                    {{ t('Belum Ada Destinasi di Wishlist', 'Your Wishlist is Empty') }}
                </h2>
                <p class="text-sm text-[#5A6B7C] dark:text-[#94A3B8] mb-6">
                    {{ t('Tandai destinasi favorit Anda pada Katalog Destinasi untuk membandingkan harga tiket, jam buka, dan menyusun rute liburan.', 'Save your favorite spots from the Destination Catalog to compare ticket prices, opening hours, and plan your route.') }}
                </p>
                <Link
                    href="/destinasi"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-sm font-semibold transition-colors"
                >
                    <Compass class="w-4 h-4" />
                    {{ t('Cari Destinasi Wisata', 'Browse Destinations') }}
                </Link>
            </div>

            <!-- Wishlist Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <article
                    v-for="item in wishlists"
                    :key="item.id"
                    class="surface-card overflow-hidden flex flex-col justify-between group"
                >
                    <div>
                        <div class="relative h-48 overflow-hidden bg-[#12344D]">
                            <img
                                :src="item.destinasi?.foto_utama"
                                :alt="item.destinasi?.nama_destinasi"
                                :style="{ objectPosition: item.destinasi?.foto_attribution?.position || 'center' }"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0D1B2A]/80 via-transparent to-transparent"></div>
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md text-xs font-bold bg-[#12344D]/85 text-white backdrop-blur-sm">
                                {{ item.destinasi?.kategori }}
                            </span>
                            <button
                                type="button"
                                @click="removeWishlist(item.destinasi_id)"
                                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 hover:bg-[#DC2626] text-[#DC2626] hover:text-white flex items-center justify-center transition-colors shadow"
                                :title="t('Hapus dari Wishlist', 'Remove from Wishlist')"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white">
                                <span class="text-xs font-medium flex items-center gap-1">
                                    <MapPin class="w-3.5 h-3.5 text-[#28A8B3]" />
                                    {{ item.destinasi?.wilayah?.nama_wilayah }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-black/50 text-xs font-bold tabular-nums">
                                    ★ {{ item.destinasi?.rating_rata_rata == null ? t('Belum dinilai', 'Not rated') : Number(item.destinasi.rating_rata_rata).toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <PhotoCredit :photo="item.destinasi?.foto_attribution" class="px-5 pt-2 text-tinta-redup" />
                        <div class="p-5">
                            <Link
                                :href="`/destinasi/${item.destinasi?.slug || item.destinasi_id}`"
                                class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] hover:text-[#0E7C86] transition-colors line-clamp-1"
                            >
                                {{ item.destinasi?.nama_destinasi }}
                            </Link>
                            <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-1.5 line-clamp-2">
                                {{ isEn ? (item.destinasi?.deskripsi_en || item.destinasi?.deskripsi_id) : item.destinasi?.deskripsi_id }}
                            </p>

                            <div v-if="item.catatan" class="mt-3 p-2.5 rounded-lg bg-[#FAF7F0] dark:bg-[#0D1B2A] border border-[#E5DEC9] dark:border-[#243B53] text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                                <span class="font-semibold text-[#12344D] dark:text-[#F1F5F9]">{{ t('Catatan:', 'Note:') }}</span> {{ item.catatan }}
                            </div>

                            <div class="mt-4 pt-3 border-t border-[#E5DEC9] dark:border-[#243B53] flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-[#5A6B7C] dark:text-[#94A3B8] block">{{ t('Tiket Dewasa', 'Adult Ticket') }}</span>
                                    <strong class="text-sm font-bold text-[#0E7C86] dark:text-[#28A8B3] tabular-nums">
                                        {{ formatRupiah(item.destinasi?.harga_tiket_dewasa || 0) }}
                                    </strong>
                                </div>
                                <div class="text-right">
                                    <span class="text-[#5A6B7C] dark:text-[#94A3B8] block">{{ t('Keramaian Hari Ini', 'Crowd Today') }}</span>
                                    <span
                                        class="inline-block font-bold"
                                        :class="{
                                            'text-[#1F8A5B]': item.destinasi?.tingkat_keramaian_hari_ini === 'Sepi',
                                            'text-[#D97706]': item.destinasi?.tingkat_keramaian_hari_ini === 'Sedang',
                                            'text-[#DC2626]': item.destinasi?.tingkat_keramaian_hari_ini === 'Ramai',
                                        }"
                                    >
                                        {{ item.destinasi?.keramaian_persen_hari_ini == null ? t('Belum ada laporan', 'No report yet') : `${item.destinasi.tingkat_keramaian_hari_ini || ''} (${item.destinasi.keramaian_persen_hari_ini}%)` }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 pb-5 flex items-center gap-2">
                        <Link
                            :href="`/destinasi/${item.destinasi?.slug || item.destinasi_id}`"
                            class="flex-1 text-center py-2 px-3 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-xs font-semibold transition-colors"
                        >
                            {{ t('Lihat & Pesan Tiket', 'View & Book Ticket') }}
                        </Link>
                        <Link
                            href="/itinerari"
                            class="py-2 px-3 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] hover:border-[#0E7C86] text-[#12344D] dark:text-[#F1F5F9] text-xs font-semibold transition-colors"
                        >
                            + {{ t('Itinerari', 'Itinerary') }}
                        </Link>
                    </div>
                </article>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import PhotoCredit from '@/Components/destinasi/PhotoCredit.vue';
import { Link, router } from '@inertiajs/vue3';
import { Heart, CalendarRange, Compass, MapPin, Trash2 } from 'lucide-vue-next';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useAppContext } from '../../composables/useAppContext';

defineProps({
    wishlists: { type: Array, default: () => [] },
});

const { t, isEn, formatRupiah } = useAppContext();

function removeWishlist(destinasiId) {
    router.post('/wishlist/toggle', { destinasi_id: destinasiId }, { preserveScroll: true });
}
</script>

