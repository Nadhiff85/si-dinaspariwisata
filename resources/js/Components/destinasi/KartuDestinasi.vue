<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Heart, MapPin, Star, Users, ArrowUpRight } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';
import PhotoCredit from './PhotoCredit.vue';

const props = defineProps({
    destinasi: { type: Object, required: true },
    isWishlisted: { type: Boolean, default: false },
    catalogView: { type: Boolean, default: false },
});

const { t, locale, user, formatCurrency } = useAppContext();

const toggleWish = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!user.value) {
        router.visit(`/login?redirect=${encodeURIComponent('/destinasi/' + props.destinasi.slug)}&reason=wishlist`);
        return;
    }
    router.post('/wishlist/toggle', { destinasi_id: props.destinasi.id }, { preserveScroll: true });
};

const handleOpenDetail = (e) => {
    if (e) e.preventDefault();
    router.visit(`/destinasi/${props.destinasi.slug}`);
};

// Lencana keramaian: titik warna + ikon + kata (DESIGN.md: status tidak boleh hanya warna)
const getCrowdStyle = (level) => {
    if (level === 'Sepi') {
        return { dot: 'bg-[#2E8B57]', label: locale.value === 'en' ? 'Quiet' : 'Sepi' };
    }
    if (level === 'Ramai') {
        return { dot: 'bg-[#C2492E]', label: locale.value === 'en' ? 'Busy' : 'Ramai' };
    }
    if (level !== 'Sedang') return { dot: 'bg-gray-400', label: t('Belum ada laporan', 'No report yet') };
    return { dot: 'bg-[#E0A422]', label: locale.value === 'en' ? 'Moderate' : 'Sedang' };
};

</script>

<template>
    <article
        :class="{ 'destination-card': catalogView }"
        class="kartu-togean group flex flex-col overflow-hidden transition duration-200 hover:-translate-y-1 hover:shadow-angkat"
    >
        <!-- Foto 4:3 -->
        <div :class="catalogView ? 'aspect-[16/10]' : 'aspect-[4/3]'" class="relative w-full overflow-hidden bg-navy">
            <img
                :src="destinasi.foto_utama"
                :alt="destinasi.nama_destinasi"
                :style="{ objectPosition: destinasi.foto_attribution?.position || 'center' }"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <span v-if="destinasi.foto_attribution?.archive" class="absolute bottom-3 left-3 rounded-md bg-navy/85 px-2 py-1 text-[10px] font-semibold text-white">{{ t('Foto arsip', 'Archive photo') }}</span>

            <span class="absolute top-3 left-3 rounded-lg bg-white/95 px-2.5 py-1 text-xs font-semibold text-utama-teks">
                {{ destinasi.kategori }}
            </span>

            <button
                type="button"
                @click="toggleWish"
                :aria-label="`${isWishlisted ? t('Hapus dari wishlist:', 'Remove from wishlist:') : t('Simpan ke wishlist:', 'Save to wishlist:')} ${destinasi.nama_destinasi}`"
                :aria-pressed="isWishlisted"
                class="absolute top-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-tinta transition hover:bg-white active:scale-95 cursor-pointer"
            >
                <Heart
                    class="h-4 w-4"
                    :class="isWishlisted ? 'fill-[#C2492E] text-[#C2492E]' : 'text-[#12344D]'"
                />
            </button>
        </div>

        <PhotoCredit :photo="destinasi.foto_attribution" class="px-4 pt-2 text-tinta-redup" />

        <!-- Isi kartu -->
        <div class="flex flex-1 flex-col p-4">
            <div class="flex items-center justify-between gap-2 text-xs text-tinta-redup">
                <span class="flex min-w-0 items-center gap-1">
                    <MapPin class="h-3.5 w-3.5 shrink-0 text-utama" />
                    <span class="truncate">{{ destinasi.wilayah?.nama_wilayah || 'Sulawesi Tengah' }}</span>
                </span>
                <span class="flex shrink-0 items-center gap-1 font-semibold text-tinta tabular-nums">
                    <Star class="h-3.5 w-3.5 fill-emas text-emas" />
                    {{ Number(destinasi.rating_rata_rata || 0).toFixed(1) }}
                    <span class="font-normal text-tinta-redup">({{ destinasi.jumlah_ulasan || 0 }})</span>
                </span>
            </div>

            <a
                :href="`/destinasi/${destinasi.slug}`"
                @click.prevent="handleOpenDetail"
                class="font-heading mt-1.5 text-lg font-semibold leading-snug text-tinta transition-colors hover:text-utama-teks cursor-pointer"
            >
                {{ destinasi.nama_destinasi }}
            </a>
            <p class="mt-1 mb-4 line-clamp-2 text-sm leading-relaxed text-tinta-redup">
                {{ locale === 'en' ? (destinasi.deskripsi_en || destinasi.deskripsi_id) : destinasi.deskripsi_id }}
            </p>

            <div class="mt-auto flex items-center justify-between gap-2 border-t border-garis pt-3">
                <div class="min-w-0">
                    <span class="block text-sm font-semibold text-tinta tabular-nums">
                        {{ formatCurrency(destinasi.harga_tiket_dewasa) }}
                    </span>
                    <span
                        v-if="destinasi.tingkat_keramaian_hari_ini"
                        class="mt-0.5 inline-flex items-center gap-1.5 text-xs text-tinta-redup"
                    >
                        <span class="h-2 w-2 rounded-full" :class="getCrowdStyle(destinasi.tingkat_keramaian_hari_ini).dot"></span>
                        <Users class="h-3 w-3" />
                        {{ getCrowdStyle(destinasi.tingkat_keramaian_hari_ini).label }}
                    </span>
                </div>

                <Link
                    :href="`/destinasi/${destinasi.slug}`"
                    class="inline-flex h-9 shrink-0 items-center gap-1 rounded-xl border border-utama px-3 text-sm font-semibold text-utama-teks transition hover:bg-utama hover:text-white active:scale-[0.98] cursor-pointer"
                >
                    <span>{{ locale === 'en' ? 'Details' : 'Detail' }}</span>
                    <ArrowUpRight class="h-3.5 w-3.5" />
                </Link>
            </div>
        </div>
    </article>
</template>
