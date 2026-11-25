<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    type: { type: String, required: true },
    options: { type: Object, default: () => ({}) },
    series: { type: Array, default: () => [] },
    height: { type: [Number, String], default: 300 },
});
const { t, locale, theme } = useAppContext();
const container = ref(null);
const width = ref(600);
let observer;
onMounted(() => {
    observer = new ResizeObserver(([entry]) => {
        if (entry.contentRect.width > 0) width.value = Math.round(entry.contentRect.width);
    });
    if (container.value) observer.observe(container.value);
});
onBeforeUnmount(() => observer?.disconnect());

const compact = computed(() => width.value < 430);
const circular = computed(() => ['pie', 'donut', 'radialBar'].includes(props.type));
const horizontal = computed(() => props.options.plotOptions?.bar?.horizontal);
const formatLabel = (value) => {
    const label = String(value ?? '').replace(/^(Kabupaten|Kota) /, '');
    const limit = compact.value ? 18 : 30;
    return label.length > limit ? `${label.slice(0, limit - 1)}…` : label;
};
const chartHeight = computed(() => {
    const requested = Number(props.height) || 300;
    const points = props.series[0]?.data?.length || 0;
    if (horizontal.value) return Math.max(requested, 85 + points * 30);
    if (circular.value) return Math.max(requested, 290 + Math.ceil((props.options.labels?.length || 0) / 2) * 23);
    return requested;
});
const chartOptions = computed(() => {
    const original = props.options;
    const text = theme.value === 'gelap' ? '#DCE7EF' : '#556877';
    const yaxis = original.yaxis;
    const yDefaults = { labels: { maxWidth: compact.value ? 105 : 155, style: { fontSize: '11px', colors: text } } };
    const normalizedAxis = Array.isArray(yaxis) ? yaxis.map(axis => ({
        ...axis,
        title: { ...axis.title, ...(compact.value ? { text: undefined } : {}) },
        labels: { ...yDefaults.labels, ...axis.labels, ...(compact.value ? { formatter: value => new Intl.NumberFormat(locale.value === 'en' ? 'en-US' : 'id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value) } : {}) },
    })) : {
        ...yaxis,
        labels: { ...yDefaults.labels, ...yaxis?.labels, ...(horizontal.value ? { formatter: formatLabel } : {}) },
    };
    return {
        ...original,
        chart: { ...original.chart, width: '100%', parentHeightOffset: 0, foreColor: text, fontFamily: 'Inter, sans-serif', redrawOnParentResize: true, redrawOnWindowResize: true, animations: { enabled: false }, toolbar: { show: false } },
        theme: { ...original.theme, mode: theme.value === 'gelap' ? 'dark' : 'light' },
        tooltip: { ...original.tooltip, theme: theme.value === 'gelap' ? 'dark' : 'light' },
        noData: { text: t('Belum ada data untuk pilihan ini.', 'No data for this selection.'), style: { color: text, fontSize: '13px' } },
        legend: { ...original.legend, position: 'bottom', fontSize: '11px', fontWeight: 500, labels: { colors: text }, itemMargin: { horizontal: 7, vertical: 4 } },
        ...(!circular.value ? {
            xaxis: {
                ...original.xaxis,
                labels: { hideOverlappingLabels: true, rotate: compact.value && props.type !== 'radar' && !horizontal.value ? -45 : 0, trim: true, maxHeight: 72, ...original.xaxis?.labels, style: { fontSize: compact.value ? '10px' : '11px', colors: text } },
                ...(props.type !== 'heatmap' && props.type !== 'radar' && !horizontal.value ? { tickAmount: compact.value ? 5 : 10 } : {}),
            },
            yaxis: normalizedAxis,
        } : {}),
        ...(props.type === 'radar' ? { plotOptions: { ...original.plotOptions, radar: { ...original.plotOptions?.radar, size: compact.value ? 72 : 95 } } } : {}),
        ...(['pie', 'donut'].includes(props.type) ? { dataLabels: { ...original.dataLabels, formatter: (value) => value >= 7 ? `${new Intl.NumberFormat(locale.value === 'en' ? 'en-US' : 'id-ID', { maximumFractionDigits: 0 }).format(value)}%` : '', dropShadow: { enabled: false } } } : {}),
    };
});
</script>

<template>
    <div ref="container" class="responsive-chart min-w-0 w-full" role="img" :aria-label="options.title?.text || t('Grafik interaktif; data tersedia melalui tombol tabel.', 'Interactive chart; data is available using the table button.')">
        <VueApexCharts :type="type" :height="chartHeight" width="100%" :options="chartOptions" :series="series" />
    </div>
</template>
