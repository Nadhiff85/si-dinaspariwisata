<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { Table, BarChart3, Download, Maximize2, Minimize2, Sparkles, Database, Info } from 'lucide-vue-next';
import SkeletonChart from '@/Components/ui/SkeletonChart.vue';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    nomor: { type: [Number, String], default: '' },
    judul: { type: String, required: true },
    subjudul: { type: String, default: '' },
    jenis: { type: String, default: 'Chart' },
    insight: { type: String, default: '' },
    sumber: { type: String, default: 'Gudang Data Jelajah Sulteng' },
    tahunData: { type: String, default: '' },
    caraBaca: { type: String, default: '' },
    compact: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    eager: { type: Boolean, default: false },
    publicView: { type: Boolean, default: false },
    tableHeaders: { type: Array, default: () => [] },
    tableRows: { type: Array, default: () => [] },
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
});

const { t } = useAppContext();
const showTable = ref(false);
const isExpanded = ref(false);
const cardRef = ref(null);
const hasEnteredViewport = ref(props.eager);
let observer = null;
let previousOverflow = '';
let previousFocus = null;
watch(isExpanded, async (expanded) => {
    if (expanded) {
        previousOverflow = document.body.style.overflow;
        previousFocus = document.activeElement;
        document.body.style.overflow = 'hidden';
        await nextTick();
        cardRef.value?.focus();
    } else {
        document.body.style.overflow = previousOverflow;
        previousFocus?.focus();
    }
});

const closeExpandedOnEscape = (event) => {
    if (event.key === 'Escape' && isExpanded.value) {
        isExpanded.value = false;
    }
};

const keepExpandedFocus = (event) => {
    if (!isExpanded.value || event.key !== 'Tab') return;
    const controls = [...cardRef.value.querySelectorAll('button, a[href], input, select, textarea, [tabindex="0"]')].filter(control => !control.disabled && control.checkVisibility());
    const first = controls[0];
    const last = controls.at(-1);
    if (event.shiftKey && (document.activeElement === first || document.activeElement === cardRef.value)) {
        event.preventDefault();
        last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
    }
};

onMounted(() => {
    window.addEventListener('keydown', closeExpandedOnEscape);
    if (props.eager || typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        hasEnteredViewport.value = true;
        return;
    }
    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    hasEnteredViewport.value = true;
                    observer?.disconnect();
                    observer = null;
                    break;
                }
            }
        },
        { rootMargin: '280px 0px' }
    );
    if (cardRef.value) {
        observer.observe(cardRef.value);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', closeExpandedOnEscape);
    observer?.disconnect();
    if (isExpanded.value) document.body.style.overflow = previousOverflow;
});

const effectiveRows = computed(() => {
    if (props.tableRows && props.tableRows.length) return props.tableRows;
    if (props.rows && props.rows.length) return props.rows;
    return [];
});

const effectiveHeaders = computed(() => {
    if (props.tableHeaders && props.tableHeaders.length) return props.tableHeaders;
    if (props.columns && props.columns.length) return props.columns.map((c) => c.label || c.key);
    if (effectiveRows.value.length && typeof effectiveRows.value[0] === 'object' && !Array.isArray(effectiveRows.value[0])) {
        return Object.keys(effectiveRows.value[0]);
    }
    return [];
});

const normalizedRows = computed(() => {
    if (!effectiveRows.value.length) return [];
    if (props.columns && props.columns.length) {
        return effectiveRows.value.map((r) => props.columns.map((c) => r[c.key] ?? '-'));
    }
    return effectiveRows.value.map((r) => (Array.isArray(r) ? r : Object.values(r)));
});

const parsedBadges = computed(() => {
    const raw = String(props.nomor || '').trim();
    if (!raw) {
        return { code: '', category: props.jenis !== 'Chart' ? props.jenis : 'Visualisasi Data' };
    }
    if (raw.includes('—')) {
        const [left, ...rest] = raw.split('—');
        const right = rest.join('—').trim();
        return {
            code: left.trim().replace(/^Chart\s*/i, ''),
            category: props.jenis && props.jenis !== 'Chart' ? `${right} • ${props.jenis}` : right,
        };
    }
    const cleanNum = raw.replace(/^Chart\s*#?/i, '');
    return {
        code: `#${cleanNum}`,
        category: props.jenis !== 'Chart' ? props.jenis : 'Visualisasi Data',
    };
});

const exportCsv = () => {
    if (!normalizedRows.value.length) return;
    const headers = effectiveHeaders.value;
    const csvLines = [
        headers.join(','),
        ...normalizedRows.value.map((vals) => {
            return vals.map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',');
        }),
    ];
    const blob = new Blob([csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `visualisasi_${String(props.nomor || 'data').replace(/[^a-zA-Z0-9]/g, '_')}_jelajah_sulteng.csv`;
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Teleport to="body" :disabled="!isExpanded">
    <div v-if="isExpanded" class="fixed inset-0 z-[60] bg-black/50" @click="isExpanded = false"></div>
    <article
        ref="cardRef"
        :role="isExpanded ? 'dialog' : undefined"
        :aria-modal="isExpanded ? true : undefined"
        :aria-label="judul"
        :tabindex="isExpanded ? -1 : undefined"
        @keydown="keepExpandedFocus"
        :class="[
            'chart-card kartu-togean flex min-w-0 flex-col justify-between p-5 transition-all duration-200 hover:border-utama/45',
            isExpanded ? 'chart-expanded fixed inset-3 z-[70] overflow-y-auto shadow-2xl sm:inset-6' : 'relative h-full',
        ]"
    >
        <!-- Header Kartu Chart -->
        <div class="chart-card-header mb-4 flex items-start justify-between gap-3 border-b border-garis pb-3.5" :class="publicView ? 'flex-col sm:flex-row' : ''">
            <div class="min-w-0 flex-1" :class="publicView ? 'w-full sm:w-auto' : ''">
                <div v-if="!publicView" class="chart-card-badges mb-1.5 flex flex-wrap items-center gap-1.5">
                    <span
                        v-if="parsedBadges.code"
                        class="inline-flex items-center rounded-md bg-navy px-2 py-0.5 font-mono text-[10.5px] font-semibold tracking-wide text-white tabular-nums"
                    >
                        {{ parsedBadges.code }}
                    </span>
                    <span
                        v-if="parsedBadges.category"
                        class="inline-flex items-center rounded-md bg-utama-muda px-2.5 py-0.5 text-[11px] font-semibold text-utama-teks"
                    >
                        {{ parsedBadges.category }}
                    </span>
                </div>
                <h3 class="font-heading font-bold leading-snug text-tinta" :class="publicView ? 'text-base sm:text-lg' : 'text-sm sm:text-[15.5px]'">
                    {{ judul }}
                </h3>
                <p v-if="subjudul" class="mt-1 text-xs leading-relaxed text-tinta-redup">
                    {{ subjudul }}
                </p>
            </div>

            <!-- Menu Aksi Kartu Chart (Tabel, CSV, Layar Penuh) -->
            <div class="chart-card-actions flex shrink-0 items-center gap-1">
                <button
                    v-if="normalizedRows.length"
                    type="button"
                    @click="showTable = !showTable"
                    :title="showTable ? t('Tampilkan grafik', 'Show chart') : t('Lihat tabel', 'View table')"
                    :aria-label="showTable ? t('Tampilkan grafik', 'Show chart') : t('Lihat tabel', 'View table')"
                    :aria-pressed="showTable"
                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-garis bg-permukaan-redup px-2.5 text-[11px] font-semibold text-tinta transition hover:border-utama hover:bg-utama-muda cursor-pointer"
                >
                    <BarChart3 v-if="showTable" class="h-3.5 w-3.5 text-utama" />
                    <Table v-else class="h-3.5 w-3.5 text-utama" />
                    <span :class="publicView ? '' : 'hidden sm:inline'">{{ showTable ? t('Grafik', 'Chart') : publicView ? t('Lihat tabel', 'View table') : t('Tabel', 'Table') }}</span>
                </button>
                <button
                    v-if="normalizedRows.length"
                    type="button"
                    @click="exportCsv"
                    :title="t('Unduh data CSV', 'Download CSV data')"
                    :aria-label="t('Unduh data CSV', 'Download CSV data')"
                    class="inline-flex h-8 items-center gap-1 rounded-lg border border-garis bg-permukaan-redup px-2 text-[11px] font-semibold text-tinta transition hover:border-utama hover:bg-utama-muda cursor-pointer"
                >
                    <Download class="h-3.5 w-3.5 text-utama" />
                    <span v-if="!publicView" class="hidden xl:inline">CSV</span>
                </button>
                <button
                    type="button"
                    @click="isExpanded = !isExpanded"
                    :title="isExpanded ? t('Kecilkan', 'Exit expanded view') : t('Perbesar grafik', 'Expand chart')"
                    :aria-label="isExpanded ? t('Kecilkan', 'Exit expanded view') : t('Perbesar grafik', 'Expand chart')"
                    :aria-pressed="isExpanded"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-garis bg-permukaan-redup text-tinta transition hover:border-utama hover:bg-utama-muda cursor-pointer"
                >
                    <Minimize2 v-if="isExpanded" class="h-3.5 w-3.5" />
                    <Maximize2 v-else class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <!-- Area Chart, Skeleton Loading, atau Tabel Pengganti (Aksesibilitas WCAG AA) -->
        <div
            v-if="caraBaca"
            class="mb-3.5 flex items-start gap-2.5 rounded-xl border border-garis/70 bg-permukaan-redup/80 px-3.5 py-2.5 text-xs text-tinta shadow-2xs"
        >
            <Info class="mt-0.5 h-4 w-4 shrink-0 text-utama" />
            <div class="leading-relaxed">
                <strong class="font-bold text-utama-teks">{{ t('Panduan Grafik:', 'Chart Guide:') }}</strong>
                <span class="ml-1 text-tinta-redup">{{ caraBaca }}</span>
            </div>
        </div>
        <div class="chart-card-body my-auto min-w-0 w-full" :class="{ 'min-h-[260px]': !compact }">
            <SkeletonChart v-if="loading || !hasEnteredViewport" height="275px" />
            <div v-else-if="!showTable" class="min-w-0 w-full muncul-halus">
                <slot />
            </div>
            <div v-else class="max-h-[310px] overflow-auto rounded-xl border border-garis gulir-tipis muncul-halus">
                <table class="w-full border-collapse text-left text-xs">
                    <thead class="sticky top-0 bg-navy text-white">
                        <tr>
                            <th
                                v-for="(h, idx) in effectiveHeaders"
                                :key="idx"
                                class="px-3 py-2 font-semibold"
                            >
                                {{ h }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis bg-permukaan">
                        <tr
                            v-for="(rowVals, rIdx) in normalizedRows"
                            :key="rIdx"
                            class="hover:bg-utama-muda/30"
                        >
                            <td
                                v-for="(val, cIdx) in rowVals"
                                :key="cIdx"
                                class="px-3 py-2 tabular-nums text-tinta"
                            >
                                {{ val }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Baris Insight & Catatan Sumber Data -->
        <div class="mt-4 space-y-2 border-t border-garis pt-3 text-xs">
            <div
                v-if="insight"
                class="flex items-start gap-2 rounded-xl bg-utama-muda/45 px-3 py-2 text-tinta"
            >
                <Sparkles class="mt-0.5 h-3.5 w-3.5 shrink-0 text-utama" />
                <p class="leading-relaxed text-[11.5px]">
                    <strong class="font-semibold text-utama-teks">{{ publicView ? t('Ringkasan:', 'Summary:') : t('Temuan Kunci:', 'Key finding:') }}</strong> {{ insight }}
                </p>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-tinta-redup">
                <span class="inline-flex min-w-0 items-center gap-1.5" :class="publicView ? '' : 'truncate'">
                    <Database class="h-3 w-3 shrink-0 text-utama" />
                    <span :class="publicView ? '' : 'truncate'">{{ publicView ? t('Sumber: ', 'Source: ') : '' }}{{ sumber }}</span>
                </span>
                <span v-if="publicView" class="shrink-0 rounded-md bg-[#0E7C86]/10 px-2 py-1 font-medium text-[#0E7C86] dark:text-[#28A8B3]">
                    {{ t('Data Resmi', 'Official Data') }}
                </span>
                <span v-else-if="normalizedRows.length" class="shrink-0 font-mono text-[10.5px] font-medium text-utama-teks">
                    {{ normalizedRows.length }} {{ t('baris', 'rows') }}
                </span>
            </div>
            <p v-if="tahunData" class="text-[11px] text-tinta-redup">
                {{ t('Tahun data', 'Data years') }} <strong class="font-semibold text-tinta">{{ tahunData }}</strong>
            </p>
        </div>
    </article>
    </Teleport>
</template>
