<script setup>
import { computed, ref } from 'vue';
import { MessageSquareText, Smile, Meh, Frown, Sparkles, Star } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    sentiment: {
        type: Object,
        default: () => ({
            total: 0,
            positif: 0,
            netral: 0,
            negatif: 0,
            persen_positif: 0,
            persen_netral: 0,
            persen_negatif: 0,
            ulasan: [],
        }),
    },
});

const { t } = useAppContext();
const activeFilter = ref('semua');

const filteredReviews = computed(() => {
    const items = props.sentiment?.ulasan || [];
    if (activeFilter.value === 'semua') {
        return items;
    }
    return items.filter((item) => item.label_sentimen === activeFilter.value);
});

const filterTabs = computed(() => [
    { id: 'semua', label: t('Semua', 'All'), count: props.sentiment?.total || 0 },
    { id: 'positif', label: t('Positif', 'Positive'), count: props.sentiment?.positif || 0 },
    { id: 'netral', label: t('Netral', 'Neutral'), count: props.sentiment?.netral || 0 },
    { id: 'negatif', label: t('Negatif', 'Negative'), count: props.sentiment?.negatif || 0 },
]);

function badgeClass(label) {
    if (label === 'positif') {
        return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    }
    if (label === 'negatif') {
        return 'bg-rose-100 text-rose-800 border-rose-200';
    }
    return 'bg-amber-100 text-amber-800 border-amber-200';
}

function labelText(label) {
    if (label === 'positif') return t('Positif', 'Positive');
    if (label === 'negatif') return t('Negatif', 'Negative');
    return t('Netral', 'Neutral');
}
</script>

<template>
    <section class="kartu-togean p-5 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-garis pb-4">
            <div>
                <div class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-utama-teks">
                    <MessageSquareText class="h-4 w-4" />
                    <span>{{ t('Analisis Sentimen Ulasan (Real-Time)', 'Real-Time Review Sentiment Analysis') }}</span>
                </div>
                <h2 class="mt-1 font-heading text-xl font-bold text-tinta">
                    {{ t('Klasifikasi Komentar Wisatawan: Positif, Netral & Negatif', 'Tourist Review Classification: Positive, Neutral & Negative') }}
                </h2>
                <p class="mt-0.5 text-xs text-tinta-redup">
                    {{ t('Data simulasi telah dibersihkan. Setiap ulasan baru yang dikirim wisatawan akan otomatis dianalisis secara kontekstual (memahami bahasa santai/slang, negasi, dan kalimat netral).', 'Simulated reviews have been cleared. Every new tourist review is automatically classified contextually (handling colloquial slang, negations, and neutral statements).') }}
                </p>
            </div>
            <div class="inline-flex items-center gap-1.5 self-start rounded-full border border-garis bg-permukaan-redup px-3 py-1.5 text-xs font-bold text-tinta">
                <Sparkles class="h-3.5 w-3.5 text-[#0E7C86]" />
                <span>{{ sentiment.total }} {{ t('Ulasan Nyata', 'Live Reviews') }}</span>
            </div>
        </div>

        <!-- 4 Kartu Ringkasan Sentimen -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-xl border border-garis bg-permukaan-redup/60 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-tinta-redup">{{ t('Total Ulasan', 'Total Reviews') }}</div>
                <div class="mt-1.5 text-2xl font-extrabold text-tinta tabular-nums">{{ sentiment.total }}</div>
                <div class="mt-1 text-[11px] text-tinta-redup">{{ t('100% dari input pengguna', '100% real user input') }}</div>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-emerald-800">
                    <span>{{ t('Positif', 'Positive') }}</span>
                    <Smile class="h-4 w-4 text-emerald-600" />
                </div>
                <div class="mt-1.5 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-900 tabular-nums">{{ sentiment.positif }}</span>
                    <span class="text-xs font-bold text-emerald-700">({{ sentiment.persen_positif }}%)</span>
                </div>
                <div class="mt-1 text-[11px] text-emerald-700">{{ t('Puas, pujian & rekomendasi', 'Satisfied & positive feedback') }}</div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-amber-800">
                    <span>{{ t('Netral', 'Neutral') }}</span>
                    <Meh class="h-4 w-4 text-amber-600" />
                </div>
                <div class="mt-1.5 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-amber-900 tabular-nums">{{ sentiment.netral }}</span>
                    <span class="text-xs font-bold text-amber-700">({{ sentiment.persen_netral }}%)</span>
                </div>
                <div class="mt-1 text-[11px] text-amber-700">{{ t('Informatif, standar & seimbang', 'Informative or balanced') }}</div>
            </div>

            <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-4">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-rose-800">
                    <span>{{ t('Negatif', 'Negative') }}</span>
                    <Frown class="h-4 w-4 text-rose-600" />
                </div>
                <div class="mt-1.5 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-rose-900 tabular-nums">{{ sentiment.negatif }}</span>
                    <span class="text-xs font-bold text-rose-700">({{ sentiment.persen_negatif }}%)</span>
                </div>
                <div class="mt-1 text-[11px] text-rose-700">{{ t('Keluhan kebersihan, harga & layanan', 'Complaints & issues') }}</div>
            </div>
        </div>

        <!-- Bar Proporsi Sentimen -->
        <div v-if="sentiment.total > 0" class="space-y-1.5">
            <div class="flex h-3 w-full overflow-hidden rounded-full bg-permukaan-redup border border-garis">
                <div class="bg-emerald-500 transition-all" :style="{ width: sentiment.persen_positif + '%' }" :title="`Positif ${sentiment.persen_positif}%`" />
                <div class="bg-amber-400 transition-all" :style="{ width: sentiment.persen_netral + '%' }" :title="`Netral ${sentiment.persen_netral}%`" />
                <div class="bg-rose-500 transition-all" :style="{ width: sentiment.persen_negatif + '%' }" :title="`Negatif ${sentiment.persen_negatif}%`" />
            </div>
        </div>

        <!-- Tab Filter Kelompok -->
        <div class="flex flex-wrap items-center gap-2">
            <button
                v-for="tab in filterTabs"
                :key="tab.id"
                type="button"
                @click="activeFilter = tab.id"
                class="rounded-lg border px-3 py-1.5 text-xs font-bold transition cursor-pointer"
                :class="activeFilter === tab.id
                    ? 'border-[#12344D] bg-[#12344D] text-white'
                    : 'border-garis bg-permukaan text-tinta hover:bg-permukaan-redup'"
            >
                {{ tab.label }} <span class="ml-1 opacity-80">({{ tab.count }})</span>
            </button>
        </div>

        <!-- Empty State saat belum ada ulasan masuk -->
        <div
            v-if="filteredReviews.length === 0"
            class="rounded-xl border border-dashed border-garis bg-permukaan-redup/40 p-8 text-center space-y-2"
        >
            <MessageSquareText class="mx-auto h-8 w-8 text-tinta-redup/60" />
            <p class="text-sm font-bold text-tinta">
                {{ sentiment.total === 0
                    ? t('Belum ada ulasan wisatawan yang masuk', 'No tourist reviews submitted yet')
                    : t('Tidak ada ulasan pada kategori ini', 'No reviews in this category') }}
            </p>
            <p class="text-xs text-tinta-redup max-w-xl mx-auto">
                {{ t('Seluruh data ulasan simulasi sudah dihapus. Begitu wisatawan mengirim ulasan pada halaman detail destinasi, komentar akan langsung dikelompokkan otomatis ke Positif, Netral, atau Negatif di panel ini.', 'All simulated reviews have been removed. Once a tourist submits a review on a destination page, it will automatically be classified into Positive, Neutral, or Negative here.') }}
            </p>
        </div>

        <!-- Daftar Komentar Terkelompok -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <article
                v-for="rev in filteredReviews"
                :key="rev.id"
                class="rounded-xl border border-garis bg-permukaan p-4 space-y-2.5"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="text-sm font-bold text-tinta">{{ rev.nama_pengguna }} · <span class="text-utama-teks">{{ rev.destinasi_nama }}</span></div>
                        <div class="text-[11px] text-tinta-redup flex items-center gap-2">
                            <span>{{ rev.tanggal }}</span>
                            <span class="inline-flex items-center gap-0.5 font-bold text-amber-600">
                                <Star class="h-3 w-3 fill-amber-400 text-amber-500" /> {{ rev.rating }}/5
                            </span>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wide"
                        :class="badgeClass(rev.label_sentimen)"
                    >
                        {{ labelText(rev.label_sentimen) }}
                    </span>
                </div>

                <p class="text-sm text-tinta leading-relaxed">“{{ rev.teks_ulasan }}”</p>

                <div v-if="(rev.kata_positif && rev.kata_positif.length) || (rev.kata_negatif && rev.kata_negatif.length)" class="flex flex-wrap gap-1.5 pt-1 border-t border-garis/60 text-[10px]">
                    <span
                        v-for="kw in rev.kata_positif"
                        :key="'p-' + kw"
                        class="rounded-md bg-emerald-50 border border-emerald-200 px-2 py-0.5 font-semibold text-emerald-700"
                    >
                        + {{ kw }}
                    </span>
                    <span
                        v-for="kw in rev.kata_negatif"
                        :key="'n-' + kw"
                        class="rounded-md bg-rose-50 border border-rose-200 px-2 py-0.5 font-semibold text-rose-700"
                    >
                        − {{ kw }}
                    </span>
                </div>
            </article>
        </div>
    </section>
</template>
