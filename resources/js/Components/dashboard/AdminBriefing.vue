<script setup>
import { ArrowRight, Sparkles, TrendingUp } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';

defineProps({
    recommendations: { type: Array, default: () => [] },
    activeCount: { type: Number, default: 0 },
    destinations: { type: Array, default: () => [] },
    recommendationText: { type: Function, required: true },
});
defineEmits(['recommendations', 'manage', 'inspect']);
const { t, formatNumber } = useAppContext();
</script>

<template>
    <div class="briefing-panels">
        <section class="briefing-panel kartu-togean" aria-labelledby="briefing-priorities">
            <div class="briefing-panel-heading">
                <div>
                    <p class="briefing-eyebrow"><Sparkles />{{ t('Tindak lanjut dinas', 'Department follow-up') }}</p>
                    <h3 id="briefing-priorities">{{ t('Prioritas yang perlu perhatian', 'Priorities needing attention') }}</h3>
                </div>
                <span class="briefing-count">{{ activeCount }} {{ t('aktif', 'active') }}</span>
            </div>
            <p class="briefing-panel-description">{{ t('Tiga rekomendasi aktif dengan prioritas tertinggi. Tinjau bukti sebelum mengambil tindakan.', 'The three highest-priority active recommendations. Review the evidence before taking action.') }}</p>
            <ol v-if="recommendations.length" class="briefing-priorities">
                <li v-for="(item, index) in recommendations" :key="item.id">
                    <span class="briefing-rank">{{ index + 1 }}</span>
                    <div class="min-w-0">
                        <div class="briefing-priority-meta">
                            <span class="briefing-priority" :class="{ 'is-high': item.prioritas === 'tinggi' }">{{ t({ tinggi: 'Prioritas tinggi', sedang: 'Prioritas sedang', rendah: 'Prioritas rendah' }[item.prioritas] || 'Prioritas lainnya', { tinggi: 'High priority', sedang: 'Medium priority', rendah: 'Low priority' }[item.prioritas] || 'Other priority') }}</span>
                            <span>{{ item.kode_aturan }}</span>
                        </div>
                        <h4>{{ recommendationText(item, 'judul') }}</h4>
                        <p>{{ item.data_pendukung || recommendationText(item, 'kondisi_terdeteksi') }}</p>
                    </div>
                </li>
            </ol>
            <p v-else class="briefing-empty">{{ t('Tidak ada rekomendasi aktif yang menunggu tindak lanjut.', 'No active recommendations are awaiting follow-up.') }}</p>
            <button type="button" class="briefing-panel-action" @click="$emit('recommendations')">{{ t('Tinjau seluruh rekomendasi', 'Review all recommendations') }}<ArrowRight /></button>
        </section>

        <section class="briefing-panel kartu-togean" aria-labelledby="briefing-ranking">
            <div class="briefing-panel-heading">
                <div>
                    <p class="briefing-eyebrow"><TrendingUp />{{ t('Kinerja destinasi', 'Destination performance') }}</p>
                    <h3 id="briefing-ranking">{{ t('Destinasi dengan skor tertinggi', 'Highest-scoring destinations') }}</h3>
                </div>
                <span class="briefing-count">{{ t('Skor / 100', 'Score / 100') }}</span>
            </div>
            <p class="briefing-panel-description">{{ t('Enam skor komposit tertinggi dari data analitik yang tersedia. Pilih destinasi untuk melihat detail.', 'The six highest composite scores in the available analytics data. Select a destination to inspect its details.') }}</p>
            <ol v-if="destinations.length" class="briefing-ranking">
                <li v-for="(destination, index) in destinations" :key="destination.id">
                    <button type="button" @click="$emit('inspect', destination)">
                        <span class="briefing-rank">{{ index + 1 }}</span>
                        <span class="briefing-destination">
                            <strong>{{ destination.nama_destinasi }}</strong>
                            <span>{{ destination.wilayah?.nama_wilayah || t('Wilayah belum tersedia', 'Region unavailable') }}</span>
                        </span>
                        <span class="briefing-score">{{ formatNumber(Number(destination.briefingScore)) }}</span>
                        <ArrowRight class="briefing-row-arrow" />
                    </button>
                </li>
            </ol>
            <p v-else class="briefing-empty">{{ t('Skor destinasi belum tersedia untuk pilihan filter ini.', 'Destination scores are unavailable for these filters.') }}</p>
            <button type="button" class="briefing-panel-action" @click="$emit('manage')">{{ t('Kelola seluruh destinasi', 'Manage all destinations') }}<ArrowRight /></button>
        </section>
    </div>
    <div class="briefing-chart-heading">
        <div>
            <p class="briefing-eyebrow">{{ t('Pemantauan kunjungan', 'Visitor monitoring') }}</p>
            <h3>{{ t('Tren, proyeksi, dan sebaran wisatawan', 'Visitor trends, forecasts, and distribution') }}</h3>
        </div>
        <span>{{ t('4 visualisasi utama', '4 key visualisations') }}</span>
    </div>
</template>
