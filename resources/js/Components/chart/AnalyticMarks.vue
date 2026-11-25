<script setup>
import { computed } from 'vue';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    mode: { type: String, required: true },
    rows: { type: Array, default: () => [] },
    maximum: { type: Number, default: 5 },
    suffix: { type: String, default: '' },
});

const { t, formatNumber } = useAppContext();
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const profiles = computed(() => {
    const maximum = Math.max(1, ...props.rows.flatMap(row => row.data.map(point => Number(point.y))));
    return props.rows.map(row => ({
        year: row.name,
        points: row.data.map(point => ({
            month: point.x,
            value: Number(point.y),
            x: 8 + months.indexOf(point.x) * 28,
            y: 54 - Number(point.y) / maximum * 46,
        })),
    }));
});
const finiteRows = computed(() => props.rows.filter(row => row.value != null && Number.isFinite(Number(row.value))));
const waffleGroups = computed(() => {
    const groups = finiteRows.value.filter(row => Number(row.value) > 0);
    const total = groups.reduce((sum, row) => sum + Number(row.value), 0);
    let cumulative = 0;
    let filled = 0;

    return groups.map(row => {
        cumulative += Number(row.value);
        const nextFilled = Math.round(100 * cumulative / total);
        const cells = nextFilled - filled;
        filled = nextFilled;
        return { ...row, cells, share: Number((100 * row.value / total).toFixed(1)) };
    });
});
const markerPosition = value => props.mode === 'diverging'
    ? 50 + value / props.maximum * 50
    : value / props.maximum * 100;
</script>

<template>
    <p v-if="!rows.length || (mode === 'waffle' ? !waffleGroups.length : mode !== 'seasonal' && !finiteRows.length)" class="px-2 py-12 text-center text-sm text-tinta-redup">
        {{ t('Belum ada data untuk pilihan ini.', 'No data for this selection.') }}
    </p>
    <div v-else-if="mode === 'seasonal'" class="grid gap-2">
        <figure v-for="profile in profiles" :key="profile.year" class="grid grid-cols-[3rem_minmax(0,1fr)] items-center gap-2">
            <figcaption class="text-sm font-semibold text-tinta">{{ profile.year }}</figcaption>
            <svg viewBox="0 0 324 60" class="w-full text-utama-teks" role="img" :aria-label="profile.points.map(point => point.month + ' ' + formatNumber(point.value)).join(', ')">
                <path d="M8 54 H316" stroke="var(--warna-garis)" />
                <template v-for="(point, index) in profile.points" :key="point.month">
                    <line v-if="index && point.x - profile.points[index - 1].x === 28" :x1="profile.points[index - 1].x" :y1="profile.points[index - 1].y" :x2="point.x" :y2="point.y" stroke="currentColor" stroke-width="2" />
                    <circle :cx="point.x" :cy="point.y" r="3" fill="currentColor">
                        <title>{{ point.month }} · {{ formatNumber(point.value) }}</title>
                    </circle>
                </template>
            </svg>
        </figure>
        <div class="ml-14 flex justify-between text-xs text-tinta-redup" aria-hidden="true"><span>Jan</span><span>{{ t('Jun', 'Jun') }}</span><span>{{ t('Des', 'Dec') }}</span></div>
    </div>
    <div v-else-if="mode === 'waffle'" class="space-y-4 py-3">
        <div aria-hidden="true" class="mx-auto grid max-w-52 grid-cols-10 gap-1">
            <template v-for="group in waffleGroups" :key="group.label">
                <span v-for="cell in group.cells" :key="cell" class="aspect-square rounded-sm" :style="{ backgroundColor: group.color }"></span>
            </template>
        </div>
        <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-tinta">
            <li v-for="group in waffleGroups" :key="group.label" class="inline-flex items-center gap-1.5">
                <span aria-hidden="true" class="size-2.5 rounded-sm" :style="{ backgroundColor: group.color }"></span>
                {{ group.label }} {{ formatNumber(group.share) }}%
            </li>
        </ul>
    </div>
    <div v-else class="space-y-3 px-2 pb-3 pt-1 text-sm">
        <div v-for="row in finiteRows" :key="row.label" class="grid grid-cols-[minmax(0,2fr)_minmax(3rem,3fr)_3.75rem] items-center gap-3">
            <span class="break-words text-tinta">{{ row.label }}</span>
            <div aria-hidden="true" class="relative h-4">
                <span class="absolute inset-x-0 top-1/2 h-px bg-garis"></span>
                <span v-if="mode === 'diverging'" class="absolute inset-y-0 left-1/2 w-px bg-tinta-redup"></span>
                <span v-if="mode !== 'dot'"
                    class="absolute top-1/2 -translate-y-1/2 rounded-sm"
                    :class="mode === 'diverging' ? (row.value < 0 ? 'h-2 bg-[#C2492E]' : 'h-2 bg-[#0E7C86]') : 'h-px bg-utama-teks'"
                    :style="{
                        left: (mode === 'diverging' ? Math.min(50, markerPosition(row.value)) : 0) + '%',
                        width: (mode === 'diverging' ? Math.abs(markerPosition(row.value) - 50) : markerPosition(row.value)) + '%',
                    }"
                ></span>
                <span v-if="['lollipop', 'dot'].includes(mode)" class="absolute top-1/2 size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-utama-teks" :style="{ left: markerPosition(row.value) + '%' }"></span>
            </div>
            <span class="text-right tabular-nums text-tinta">{{ mode === 'diverging' && row.value > 0 ? '+' : '' }}{{ formatNumber(row.value) }}{{ suffix }}</span>
        </div>
        <div class="grid grid-cols-[minmax(0,2fr)_minmax(3rem,3fr)_3.75rem] gap-3 text-xs text-tinta-redup" aria-hidden="true">
            <span></span>
            <div class="flex justify-between"><span>{{ mode === 'diverging' ? -maximum : 0 }}{{ suffix }}</span><span v-if="mode === 'diverging'">0</span><span>{{ formatNumber(maximum) }}{{ suffix }}</span></div>
        </div>
    </div>
</template>
