<script setup>
import { computed, ref } from 'vue';
import { useAppContext } from '@/composables/useAppContext';
import KartuChart from '@/Components/chart/KartuChart.vue';
import ResponsiveChart from '@/Components/chart/ResponsiveChart.vue';
import AnalyticMarks from '@/Components/chart/AnalyticMarks.vue';
import 'apexcharts/features/stats';
import 'apexcharts/features/waterfall';
import 'apexcharts/features/dumbbell';
import csv from '../../../../database/pariwisata_resmi_2025.csv?raw';
import csvUrl from '../../../../database/pariwisata_resmi_2025.csv?url';
import hotelCsv from '../../../../database/perhotelan_resmi_pilihan.csv?raw';
import hotelCsvUrl from '../../../../database/perhotelan_resmi_pilihan.csv?url';

const source = 'https://pariwisata.sultengprov.go.id/images/2026/02Pemasaran/Rekap_Kunjungan_Wisatawan_Thn_2025_update_-_03_feb.pdf';
const records = csv.trim().split(/\r?\n/).slice(1).map(line => {
    const [metric, period, region, value] = line.split(',');
    return { metric, period, region, value: value === '' ? null : Number(value) };
});
const hotelRows = hotelCsv.trim().split(/\r?\n/).slice(1).map(line => {
    const [label, occupancy, domestic, international, , sourceUrl] = line.split(',');
    return { label, occupancy: Number(occupancy), domestic: Number(domestic), international: Number(international), sourceUrl };
});
const regions = [...new Set(records.map(row => row.region))].sort();
const periods = [...new Set(records.map(row => row.period))];
const dataYears = periods => [...new Set(periods.map(period => period.slice(0, 4)))].sort().join(', ');
const tourismYears = dataYears(periods);
const hotelYears = dataYears(hotelRows.map(row => row.label));
const metric = ref('perjalanan_wisnus');
const region = ref('');
const section = ref('summary');
const { t, locale, formatNumber } = useAppContext();
const percent = value => formatNumber(value) + '%';
const compactNumber = value => new Intl.NumberFormat(locale.value === 'en' ? 'en' : 'id', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
const isDomestic = computed(() => metric.value === 'perjalanan_wisnus');
const unit = computed(() => isDomestic.value ? t('Perjalanan wisnus', 'Domestic trips') : t('Kunjungan wisman', 'International visits'));
const selected = computed(() => records.filter(row => row.metric === metric.value));
const sum = rows => rows.reduce((total, row) => total + (row.value ?? 0), 0);
const monthly = computed(() => periods.map(period => {
    const rows = selected.value.filter(row => row.period === period && (!region.value || row.region === region.value));
    return { label: period, value: rows.some(row => row.value != null) ? sum(rows) : null };
}));
const annual = computed(() => regions.map(label => ({
    label, value: sum(selected.value.filter(row => row.region === label)),
})).sort((a, b) => b.value - a.value));
const quarters = computed(() => Array.from({ length: 4 }, (_, index) => {
    const rows = monthly.value.slice(index * 3, index * 3 + 3);
    return { label: t('Triwulan', 'Quarter') + ' ' + (index + 1), value: sum(rows) };
}));
const changes = computed(() => monthly.value.slice(1).map((row, index) => {
    const previous = monthly.value[index].value;
    return { label: row.label, value: row.value == null || previous == null ? null : row.value - previous };
}));
const shares = computed(() => {
    const total = sum(annual.value);
    let cumulative = 0;
    return annual.value.map(row => {
        cumulative += row.value;
        return { ...row, share: Math.round(cumulative * 1000 / total) / 10 };
    });
});
const highest = computed(() => monthly.value.reduce((peak, row) => row.value != null && (peak == null || row.value > peak.value) ? row : peak, null));
const regionSummary = computed(() => annual.value.map(row => {
    const records = selected.value.filter(record => record.region === row.label && record.value != null);
    const values = records.map(record => record.value).sort((a, b) => a - b);
    const average = sum(records) / records.length;
    const peak = records.reduce((largest, record) => record.value > largest.value ? record : largest, records[0]);
    const first = sum(records.filter(record => record.period < '2025-07'));
    const second = sum(records.filter(record => record.period >= '2025-07'));
    return {
        ...row, values, peak, first, second, average,
        seasonalVariation: average > 0 ? Math.sqrt(values.reduce((total, value) => total + (value - average) ** 2, 0) / values.length) / average : 0,
    };
}));
const round = value => Math.round(value * 10) / 10;
const distribution = rows => rows.map(row => ({
    x: row.label,
    y: [0, 0.25, 0.5, 0.75, 1].map(percentile => {
        const index = (row.values.length - 1) * percentile;
        const low = Math.floor(index);
        return row.values[low] + (row.values[Math.ceil(index)] - row.values[low]) * (index - low);
    }),
}));
const advancedCharts = computed(() => {
    const regional = regionSummary.value;
    const months = monthly.value;
    const yearTotal = sum(annual.value);
    const yearAverage = yearTotal / 12;
    let accumulated = 0;
    const cumulative = months.map(row => ({ ...row, value: accumulated += row.value ?? 0 }));
    const averages = months.map((row, index) => {
        const window = months.slice(Math.max(0, index - 2), index + 1);
        return { label: row.label, value: window.length === 3 && window.every(point => point.value != null) ? round(sum(window) / 3) : null };
    });
    const peakRows = regional.map(row => ({ label: row.label, month: row.peak.period, value: row.peak.value }))
        .sort((a, b) => b.value - a.value);
    const bySemester = regional.map(row => ({ label: row.label, first: row.first, second: row.second }));
    const semesterSeries = [
        { name: t('Semester I', 'First half'), data: bySemester.map(row => row.first) },
        { name: t('Semester II', 'Second half'), data: bySemester.map(row => row.second) },
    ];
    const quarterlyMaxima = quarters.value.map((row, index) => {
        const observed = months.slice(index * 3, index * 3 + 3).filter(point => point.value != null);
        return { label: row.label, minimum: Math.min(...observed.map(point => point.value)), maximum: Math.max(...observed.map(point => point.value)) };
    });
    const peakCounts = periods.map(label => {
        const matches = peakRows.filter(row => row.month === label);
        return {
            label,
            value: matches.length,
            wilayah_puncak: matches.length > 0 ? matches.map(m => m.label).join(', ') : '-',
        };
    });
    const semesterDifference = regional.map(row => ({ label: row.label, value: row.second - row.first })).sort((a, b) => a.value - b.value);
    const leadingRegions = regional.slice(0, 5);
    const firstRanks = [...regional].sort((a, b) => b.first - a.first).map(row => row.label);
    const secondRanks = [...regional].sort((a, b) => b.second - a.second).map(row => row.label);
    const ranks = regional.map(row => ({ label: row.label, first: firstRanks.indexOf(row.label) + 1, second: secondRanks.indexOf(row.label) + 1 }));
    const quarterlyShares = leadingRegions.map(row => ({
        label: row.label,
        values: [0, 1, 2, 3].map(index => {
            const quarter = selected.value.filter(record => record.region === row.label && periods.slice(index * 3, index * 3 + 3).includes(record.period));
            return round(sum(quarter) / row.value * 100);
        }),
    }));

    return [
        { title: t('Berapa realisasi kumulatif sepanjang tahun?', 'What was the cumulative year-to-date volume?'), kind: 'Area kumulatif', type: 'area', rows: cumulative, local: true },
        { title: t('Bagaimana arah tren setelah fluktuasi bulanan diratakan?', 'What is the trend after smoothing monthly fluctuations?'), kind: 'Rata-rata bergerak', type: 'line', rows: averages, local: true,
            series: [{ name: unit.value, data: months.map(row => row.value) }, { name: t('Rata-rata 3 bulan', '3-month moving average'), data: averages.map(row => row.value) }], options: { stroke: { width: [1, 3], dashArray: [4, 0] } } },
        { title: t('Kabupaten/kota mana yang menyumbang volume terbesar?', 'Which districts account for the largest volumes?'), kind: 'Treemap', type: 'treemap', rows: annual.value,
            insight: t(`${regional[0].label} mencatat ${formatNumber(regional[0].value)}, atau ${formatNumber(round(regional[0].value * 100 / yearTotal))}% dari total provinsi.`, `${regional[0].label} recorded ${formatNumber(regional[0].value)}, or ${formatNumber(round(regional[0].value * 100 / yearTotal))}% of the provincial total.`),
            options: { dataLabels: { enabled: true, style: { colors: ['#FFFFFF'], fontSize: '12px' } }, plotOptions: { treemap: { enableShades: false } } },
            series: [{ data: annual.value.map(row => ({ x: row.label, y: row.value })) }] },
        { title: t('Berapa volume tertinggi yang pernah dicatat tiap wilayah?', 'What was each area’s highest monthly volume?'), kind: 'Lollipop', type: 'lollipop', rows: peakRows },
        { title: t('Seberapa lebar variasi volume bulanan per wilayah?', 'How widely do monthly volumes vary by district?'), kind: 'Box plot', type: 'boxPlot',
            rows: distribution(regional).map(row => ({ label: row.x, minimum: row.y[0], q1: row.y[1], median: row.y[2], q3: row.y[3], maximum: row.y[4] })),
            series: [{ name: unit.value, data: distribution(regional) }],
            options: { xaxis: { labels: { rotate: -45, rotateAlways: true, trim: false } }, yaxis: { min: 0, labels: { formatter: compactNumber } } } },
        { title: t('Rentang volume bulanan mana yang paling sering muncul?', 'Which monthly volume range occurs most often?'), kind: 'Histogram', type: 'histogram',
            rows: selected.value.map(row => ({ label: row.region + ' · ' + row.period, value: row.value })),
            series: [{ name: t('Jumlah observasi', 'Observation count'), data: selected.value.filter(row => row.value != null).map(row => row.value) }] },
        { title: t('Bagaimana selisih semester I dan II per wilayah?', 'How large is each district’s gap between halves?'), kind: 'Dumbbell', type: 'dumbbell',
            rows: bySemester, series: semesterSeries, options: { plotOptions: { bar: { horizontal: true } } } },
        { title: t('Wilayah mana yang naik atau turun pada semester II?', 'Where did the volume rise or fall in the second half?'), kind: 'Batang divergen', type: 'diverging', rows: semesterDifference },
        {
            title: t('Bagaimana tren bulanan 5 kabupaten/kota teratas?', 'How did monthly volume trend across the top five districts?'),
            kind: 'Garis multi-wilayah',
            type: 'line',
            rows: selected.value.filter(row => leadingRegions.some(region => region.label === row.region)).map(row => ({ label: row.region + ' · ' + row.period, value: row.value })),
            series: leadingRegions.map(region => ({
                name: region.label,
                data: periods.map(p => {
                    const match = selected.value.find(row => row.region === region.label && row.period === p);
                    return match ? match.value : 0;
                }),
            })),
            options: {
                stroke: { curve: 'smooth', width: 3 },
                xaxis: { categories: periods },
                yaxis: {
                    labels: { formatter: compactNumber },
                    title: { text: t('Jumlah wisatawan (orang)', 'Visitors (people)') },
                },
                tooltip: {
                    y: {
                        formatter: val => `${formatNumber(val)} ${t('orang / perjalanan', 'visitors / trips')}`,
                    },
                },
                legend: { show: true, position: 'top', horizontalAlign: 'right' },
                markers: { size: 4, hover: { size: 6 } },
                colors: ['#0E7C86', '#E28743', '#1B6B93', '#87A922', '#C2492E'],
            },
        },
        {
            title: t('Bulan apa yang paling sering menjadi puncak daerah?', 'Which months are most often the peak across districts?'),
            kind: 'Kolom frekuensi',
            type: 'bar',
            rows: peakCounts,
            series: [{ name: t('Jumlah wilayah', 'District count'), data: peakCounts.map(row => row.value) }],
            options: {
                tooltip: {
                    y: {
                        formatter: (val, { dataPointIndex }) => {
                            const item = peakCounts[dataPointIndex];
                            if (!item || item.value === 0) {
                                return t('0 wilayah (bukan bulan puncak)', '0 districts (not a peak month)');
                            }
                            return `${val} ${t('wilayah', 'districts')}: ${item.wilayah_puncak}`;
                        },
                    },
                },
            },
        },
        { title: t('Berapa rentang bulan rendah hingga tinggi tiap triwulan?', 'How large was the monthly range within each quarter?'), kind: 'Batang rentang', type: 'rangeBar', rows: quarterlyMaxima, local: true,
            series: [{ data: quarterlyMaxima.map(row => ({ x: row.label, y: [row.minimum, row.maximum] })) }], options: { plotOptions: { bar: { horizontal: true } } } },
        { title: t('Wilayah mana yang besar tetapi lebih berfluktuasi?', 'Which large districts are also more variable?'), kind: 'Scatter plot', type: 'scatter',
            rows: regional.map(row => ({ label: row.label, volume: row.value, variation: round(row.seasonalVariation * 100) })),
            series: regional.map(row => ({ name: row.label, data: [{ x: row.value, y: round(row.seasonalVariation * 100) }] })),
            options: { xaxis: { type: 'numeric', min: 0, labels: { formatter: compactNumber }, title: { text: unit.value } }, yaxis: { min: 0, title: { text: t('Variasi bulanan (%)', 'Monthly variation (%)') }, labels: { formatter: percent } }, legend: { show: false }, tooltip: { shared: false, intersect: true, y: { formatter: percent } } } },
        {
            title: t('Bagaimana pembagian volume antarsemester?', 'How was volume split between the two halves?'),
            kind: 'Waffle',
            type: 'waffle',
            rows: [{ label: t('Semester I', 'First half'), value: sum(months.slice(0, 6)), color: '#0E7C86' }, { label: t('Semester II', 'Second half'), value: sum(months.slice(6)), color: '#C2492E' }],
            local: true,
            insight: t(
                'Volume provinsi terbagi sangat berimbang (50,1% vs 49,9%, selisih hanya 0,2%). Gunakan filter wilayah di atas untuk melihat pergeseran antarsemester pada masing-masing kabupaten.',
                'Statewide volume is almost perfectly balanced (50.1% vs 49.9%, a 0.2% gap). Use the area filter above to see semester shifts in specific districts.'
            ),
        },
        {
            title: t('Bagaimana distribusi kuartal (triwulan) di 5 wilayah teratas?', 'How is volume distributed across quarters in the top five districts?'),
            kind: 'Kolom triwulanan',
            type: 'bar',
            rows: quarterlyShares.map(row => ({
                wilayah: row.label,
                'Triwulan I (Jan–Mar)': row.values[0] + '%',
                'Triwulan II (Apr–Jun)': row.values[1] + '%',
                'Triwulan III (Jul–Sep)': row.values[2] + '%',
                'Triwulan IV (Okt–Des)': row.values[3] + '%',
            })),
            series: quarterlyShares.map(row => ({
                name: row.label,
                data: row.values,
            })),
            options: {
                plotOptions: { bar: { horizontal: false, columnWidth: '65%' } },
                xaxis: {
                    categories: [
                        t('Triwulan I (Jan–Mar)', 'Q1 (Jan–Mar)'),
                        t('Triwulan II (Apr–Jun · Lebaran)', 'Q2 (Apr–Jun · Eid)'),
                        t('Triwulan III (Jul–Sep)', 'Q3 (Jul–Sep)'),
                        t('Triwulan IV (Okt–Des · Nataru)', 'Q4 (Oct–Dec · Year-End)'),
                    ],
                },
                yaxis: {
                    min: 0,
                    max: 35,
                    labels: { formatter: value => value + '%' },
                    title: { text: t('Porsi volume tahunan (%)', 'Share of annual volume (%)') },
                },
                tooltip: {
                    y: { formatter: value => `${value}% ${t('dari total tahunan daerah', 'of district annual total')}` },
                },
                legend: { show: true, position: 'top', horizontalAlign: 'right' },
                colors: ['#0E7C86', '#E28743', '#1B6B93', '#87A922', '#C2492E'],
            },
            insight: t(
                'Triwulan II (Apr–Jun) mencatat porsi tertinggi bagi destinasi pantai Donggala (28,8%) dan Parigi (28,3%) karena libur Lebaran. Sebaliknya, Kota Palu dan Sigi mencapai porsi tertingginya pada Triwulan IV (Okt–Des) karena libur akhir tahun.',
                'Q2 (Apr–Jun) peaks for coastal Donggala (28.8%) and Parigi (28.3%) due to Eid holidays, whereas Kota Palu and Sigi peak in Q4 (Oct–Dec) due to year-end travel.'
            ),
        },
        { title: t('Seberapa besar volume triwulan terhadap rata-rata?', 'How does each quarter compare with the average?'), kind: 'Polar area', type: 'polarArea',
            rows: quarters.value.map(row => ({ label: row.label, value: round(row.value / (sum(quarters.value) / 4) * 100) })), local: true },
        { title: t('Wilayah mana yang konsisten menyumbang volume terbesar?', 'Which districts consistently contribute the most?'), kind: 'Area bertumpuk', type: 'area',
            rows: selected.value.filter(row => leadingRegions.some(region => region.label === row.region)).map(row => ({ label: row.region + ' · ' + row.period, value: row.value })),
            series: leadingRegions.map(region => ({ name: region.label, data: selected.value.filter(row => row.region === region.label).map(row => ({ x: row.period, y: row.value })) })),
            options: { chart: { stacked: true }, xaxis: { type: 'category' } } },
        { title: t('Berapa persen volume tiap wilayah terjadi pada setiap semester?', 'What share of each district’s volume occurred in each half?'), kind: 'Batang bertumpuk 100%', type: 'bar',
            rows: bySemester, series: semesterSeries, options: { chart: { stacked: true, stackType: '100%' }, plotOptions: { bar: { horizontal: true } } } },
        { title: t('Berapa kontribusi wilayah teratas terhadap total tahunan?', 'What share goes to the highest-volume district?'), kind: 'Gauge radial', type: 'radialBar',
            insight: t(` ${regional[0].label} menyumbang ${formatNumber(regional[0].value)} dari ${formatNumber(yearTotal)} yang tercatat di provinsi.`.trim(), `${regional[0].label} accounts for ${formatNumber(regional[0].value)} of the ${formatNumber(yearTotal)} recorded statewide.`),
            options: { plotOptions: { radialBar: { dataLabels: { value: { formatter: percent } } } } },
            rows: [{ label: regional[0].label, value: round(regional[0].value / yearTotal * 100) }] },
        {
            title: t('Bagaimana perubahan bulanan membentuk selisih Januari–Desember?', 'How did monthly changes add up between January and December?'),
            kind: 'Waterfall',
            type: 'waterfall',
            rows: [
                { label: `${t('Awal', 'Baseline')} (2025-01)`, value: months[0]?.value ?? 0, tipe: t('Baseline awal', 'Initial baseline') },
                ...changes.value.map(row => ({
                    label: `Δ ${row.label}`,
                    value: row.value,
                    tipe: row.value >= 0 ? t('Kenaikan (+)', 'Increase (+)') : t('Penurunan (-)', 'Decrease (-)'),
                })),
                { label: `${t('Total', 'Total')} (2025-12)`, value: months[months.length - 1]?.value ?? 0, tipe: t('Total akhir', 'Final total') },
            ],
            local: true,
            insight: t(
                'Panduan Warna: Balok Hijau menandakan kenaikan kunjungan (+) dibanding bulan lalu; Balok Merah menandakan penurunan kunjungan (-); Balok Biru Tua menandakan total realisasi volume awal (Januari) dan akhir (Desember).',
                'Color Guide: Green bars mark visitor gains (+) over prior month; Red bars mark drops (-); Dark Teal bars mark January baseline and December final total volumes.'
            ),
            series: months.some(row => row.value == null) ? [] : [{
                data: [
                    { x: `Jan (${t('Awal', 'Base')})`, y: months[0].value },
                    ...changes.value.map(row => ({
                        x: row.label.replace('2025-', '') + ' (Δ)',
                        y: row.value,
                    })),
                    { x: `Des (${t('Total', 'Total')})`, y: months[months.length - 1].value, isTotal: true },
                ],
            }],
            options: {
                xaxis: {
                    labels: { rotate: -45, rotateAlways: true, trim: false },
                },
                yaxis: {
                    labels: { formatter: compactNumber },
                },
                tooltip: {
                    y: {
                        formatter: (val, { dataPointIndex }) => {
                            if (dataPointIndex === 0) return `${formatNumber(val)} ${unit.value} (${t('Realisasi Januari', 'January Baseline')})`;
                            if (dataPointIndex === 12) return `${formatNumber(val)} ${unit.value} (${t('Realisasi Desember', 'December Total')})`;
                            const sign = val > 0 ? '+' : '';
                            const status = val > 0 ? t('Kenaikan', 'Increase') : t('Penurunan', 'Decrease');
                            return `${sign}${formatNumber(val)} ${unit.value} (${status} MoM)`;
                        },
                    },
                },
            },
        },
        { title: t('Bagaimana pola wilayah berubah menurut skala dan variasi?', 'How do scale and variability differ across districts?'), kind: 'Bubble', type: 'bubble',
            rows: regional.map(row => ({ label: row.label, share: round(row.average / yearAverage * 100), variation: round(row.seasonalVariation * 100), peak: row.peak.value })),
            series: regional.map(row => ({ name: row.label, data: [{ x: round(row.average / yearAverage * 100), y: round(row.seasonalVariation * 100), z: row.peak.value }] })),
            options: { xaxis: { type: 'numeric', min: 0, max: Math.ceil(Math.max(...regional.map(row => row.average / yearAverage * 100)) / 5) * 5 + 5, labels: { formatter: percent }, title: { text: t('Porsi rata-rata bulanan provinsi (%)', 'Share of provincial monthly mean (%)') } }, yaxis: { min: 0, labels: { formatter: percent }, title: { text: t('Variasi bulanan (%)', 'Monthly variation (%)') } }, plotOptions: { bubble: { minBubbleRadius: 5, maxBubbleRadius: 20 } }, tooltip: { shared: false, intersect: true, y: { formatter: percent }, z: { title: t('Volume bulan puncak', 'Peak-month volume'), formatter: formatNumber } }, legend: { show: false } } },
    ];
});
const charts = computed(() => [
    {
        title: t('Kapan volume mencapai puncaknya?', 'When did volume peak?'), kind: 'Garis', type: 'line', rows: monthly.value,
        insight: highest.value ? t(
            `${highest.value.label} mencatat ${formatNumber(highest.value.value)}. Gunakan pola ini untuk menyusun rencana kapasitas dan uji kembali dengan data tahun berikutnya.`,
            `${highest.value.label} recorded ${formatNumber(highest.value.value)}. Use this pattern to plan capacity and test it against the next year.`,
        ) : '',
    },
    {
        title: t('Seberapa besar perubahan antarbulan?', 'How much did volume change month to month?'), kind: 'Kolom divergen',
        type: 'bar', rows: changes.value,
        options: { plotOptions: { bar: { colors: { ranges: [{ from: -100000000, to: -1, color: '#C2492E' }] } } } },
    },
    {
        title: t('Bagaimana volume terbagi per triwulan?', 'How is volume distributed across quarters?'),
        kind: 'Donut', type: 'donut', rows: quarters.value,
    },
    {
        title: t('Wilayah mana yang mencatat volume tertinggi?', 'Which areas recorded the highest volume?'),
        kind: 'Batang horizontal', type: 'bar', rows: annual.value,
        options: { plotOptions: { bar: { horizontal: true } } },
    },
    {
        title: t('Seberapa terkonsentrasi volume antardaerah?', 'How concentrated is volume across areas?'),
        kind: 'Pareto', type: 'line', rows: shares.value,
        series: [
            { name: unit.value, type: 'column', data: shares.value.map(row => row.value) },
            { name: t('Kumulatif (%)', 'Cumulative (%)'), type: 'line', data: shares.value.map(row => row.share) },
        ],
        options: { stroke: { width: [0, 3] }, yaxis: [{ min: 0 }, { opposite: true, min: 0, max: 100 }] },
        insight: t('Gunakan peringkat untuk memilih wilayah yang perlu diprioritaskan dalam koordinasi layanan.', 'Use the ranking to prioritise areas for service coordination.'),
    },
    {
        title: t('Bagaimana pola bulanan tiap kabupaten/kota?', 'What are the monthly patterns across districts?'),
        kind: 'Heatmap', type: 'heatmap',
        rows: selected.value.map(row => ({ label: row.region + ' · ' + row.period, value: row.value })),
        series: regions.map(name => ({
            name, data: selected.value.filter(row => row.region === name).map(row => ({ x: row.period, y: row.value })),
        })),
    },
]);

const optionsFor = chart => ({
    colors: chart.type === 'donut' ? ['#0E7C86', '#12344D', '#C2492E', '#D39A25'] : ['#0E7C86', '#C2492E'],
    dataLabels: { enabled: false },
    labels: chart.rows.map(row => row.label),
    xaxis: { categories: chart.type === 'heatmap' ? periods : (chart.type === 'waterfall' ? chart.series?.[0]?.data?.map(d => d.x) : chart.rows.map(row => row.label)) },
    ...chart.options,
});
const seriesFor = chart => chart.series ?? (chart.type === 'donut'
    || ['radialBar', 'polarArea'].includes(chart.type)
    ? chart.rows.map(row => row.value)
    : [{ name: unit.value, data: chart.rows.map(row => row.value) }]);
const hotelCharts = computed(() => [
    {
        title: t('Berapa tingkat penghunian kamar hotel bintang?', 'What was star hotel room occupancy?'),
        kind: 'Kolom', type: 'bar',
        rows: hotelRows.map(row => ({ label: row.label, value: row.occupancy })),
        series: [{ name: 'TPK (%)', data: hotelRows.map(row => row.occupancy) }],
        options: { yaxis: { min: 0, max: 100, labels: { formatter: value => value + '%' } } },
    },
    {
        title: t('Berapa tamu domestik dan asing yang menginap?', 'How many domestic and international guests stayed?'),
        kind: 'Kolom bertumpuk', type: 'bar',
        rows: hotelRows.map(({ label, domestic, international }) => ({ label, domestic, international })),
        series: [
            { name: t('Domestik', 'Domestic'), data: hotelRows.map(row => row.domestic) },
            { name: t('Asing', 'International'), data: hotelRows.map(row => row.international) },
        ],
        options: { chart: { stacked: true } },
    },
    {
        title: t('Seberapa besar porsi tamu asing?', 'What share of guests came from abroad?'),
        kind: 'Dot plot persentase', type: 'dot', maximum: Math.max(1, Math.ceil(Math.max(...hotelRows.map(row => row.international * 100 / (row.domestic + row.international))))),
        rows: hotelRows.map(row => ({ label: row.label, value: Math.round(row.international / (row.domestic + row.international) * 10000) / 100 })),
    },
    {
        title: t('Bagaimana perubahan jumlah tamu di bulan yang dipublikasikan?', 'How did total guests differ between reporting months?'),
        kind: 'Kolom berkelompok', type: 'bar',
        rows: hotelRows.map(row => ({ label: row.label, total: row.domestic + row.international })),
        series: [
            { name: t('Jumlah tamu', 'Total guests'), data: hotelRows.map(row => row.domestic + row.international) },
            { name: t('Tamu domestik', 'Domestic guests'), data: hotelRows.map(row => row.domestic) },
        ],
    },
]);
const sections = computed(() => [
    { id: 'summary', label: t('Ringkasan', 'Overview'), numbers: [1, 3, 4, 24] },
    { id: 'time', label: t('Tren & periode', 'Trends & periods'), numbers: [2, 7, 8, 13, 14, 15, 16, 17, 19, 21, 25] },
    { id: 'regions', label: t('Perbandingan wilayah', 'Regional comparisons'), numbers: [5, 6, 9, 10, 11, 12, 18, 20, 22, 23, 26] },
    { id: 'hotel', label: t('Perhotelan', 'Hotels'), numbers: [27, 28, 29, 30] },
]);
const visibleCharts = computed(() => [...charts.value, ...advancedCharts.value]
    .map((chart, index) => ({ ...chart, number: index + 1 }))
    .filter(chart => sections.value.find(item => item.id === section.value).numbers.includes(chart.number)));

const readingGuides = computed(() => ({
    1: t('Menampilkan garis naik-turun volume wisatawan tiap bulan. Titik puncak tertinggi menunjukkan bulan teramai sepanjang tahun.', 'Shows the monthly tourist volume curve. The highest point marks the busiest month of the year.'),
    2: t('Menghitung perubahan volume dibanding bulan sebelumnya (MoM). Batang toska ke atas (+) berarti kunjungan bertambah; batang merah ke bawah (-) berarti kunjungan menurun dibanding bulan lalu.', 'Shows Month-over-Month (MoM) change. Teal bars pointing up (+) indicate visitor growth; red bars pointing down (-) indicate a drop compared to the previous month.'),
    3: t('Membagi total kunjungan setahun ke dalam 4 Triwulan (periode 3 bulanan). Membantu melihat kuartal mana yang menyerap wisatawan paling besar.', 'Divides total annual visitors across four 3-month quarters to identify which period captures the highest volume.'),
    4: t('Mengurutkan peringkat 13 kabupaten/kota dari yang paling banyak dikunjungi hingga yang terendah sepanjang tahun 2025.', 'Ranks the 13 regencies/cities from highest to lowest total visitor volume in 2025.'),
    5: t('Batang menunjukkan volume per wilayah. Garis kurva menunjukkan persentase kumulatif yang terkumpul dari wilayah terbesar hingga terkecil (Prinsip Pareto 80/20).', 'Bars show regional volume. The curve shows cumulative percentage from largest to smallest (Pareto principle).'),
    6: t('Tabel pemetaan matriks: Baris adalah wilayah dan kolom adalah bulan. Warna lebih pekat menunjukkan konsentrasi volume yang lebih padat; klik tombol Tabel untuk melihat angka persisnya.', 'Matrix heatmap: Rows are regions and columns are months. Darker shades indicate denser volume; click Table for exact figures.'),
    7: t('Garis area yang terus menanjak menghitung akumulasi total wisatawan yang terkumpul dari awal Januari hingga akhir Desember 2025.', 'The climbing area line tracks the cumulative accumulation of tourists from January to December 2025.'),
    8: t('Garis putus-putus meratakan fluktuasi bulanan dengan rata-rata 3 bulan agar arah tren makro terlihat lebih halus dan jelas.', 'The dashed line smooths monthly volatility with a 3-month moving average to reveal the underlying macro trend.'),
    9: t('Setiap kotak mewakili satu wilayah. Semakin luas kotaknya, semakin besar kontribusinya terhadap total pariwisata provinsi.', 'Each rectangle represents a district. Larger rectangles account for a larger share of the provincial total.'),
    10: t('Titik bulat menunjukkan rekor volume kunjungan bulanan tertinggi (angka puncak) yang pernah dicapai masing-masing kabupaten/kota.', 'Circles indicate the highest single monthly visitor peak ever recorded by each regency/city.'),
    11: t('Garis di dalam kotak adalah median. Kotak mencakup separuh tengah catatan bulanan, dan ujung garis menunjukkan nilai terendah serta tertinggi.', 'The line inside the box is the median. The box covers the middle half of monthly observations, and whiskers show the lowest and highest values.'),
    12: t('Setiap batang menghitung berapa kali kombinasi wilayah dan bulan yang volume kunjungannya masuk ke dalam rentang interval tersebut.', 'Each bar counts how many district-month observations fall within that specific volume bracket.'),
    13: t('Dua titik bulat menunjukkan volume Semester I (Jan–Jun, toska) dan Semester II (Jul–Des, merah). Garis penghubung menunjukkan selisih perubahan. Posisi Kota Palu melesat jauh di kanan karena volume kunjungannya paling masif (outlier).', 'Two dots show Semester I (Jan–Jun, teal) and Semester II (Jul–Des, red). The connecting line shows the change. Kota Palu is far on the right due to its massive visitor volume (outlier).'),
    14: t('Menghitung selisih Semester II dikurangi Semester I. Batang merah ke kiri (-) berarti kunjungan turun di paruh kedua tahun; batang toska ke kanan (+) berarti kunjungan naik.', 'Calculates the difference between Semester II and Semester I. Red bars to the left (-) mean volume fell in the second half; teal bars to the right (+) mean volume rose.'),
    15: t('Membandingkan kurva tren pergerakan wisatawan tiap bulan untuk 5 kabupaten/kota terbesar. Klik legenda nama daerah di atas grafik untuk menyembunyikan atau menampilkan wilayah tertentu.', 'Compares monthly tourist trend curves across the top five districts. Click legend items to toggle specific regions.'),
    16: t('Menghitung berapa kali suatu bulan menjadi puncak keramaian tahunan bagi 13 kabupaten/kota. Bulan dengan balok kosong (0) berarti tidak ada daerah yang memuncak di bulan tersebut (bukan musim puncak/off-season). Arahkan kursor atau klik tombol Tabel untuk melihat daftar nama daerah puncaknya.', 'Counts how often each month is the annual peak across 13 districts. Empty bars (0) mean no district peaked in that month. Hover or click Table to see the list of peak districts.'),
    17: t('Rentang batang horizontal menunjukkan jarak selisih antara bulan tersepi dan bulan teramai di dalam tiap triwulan.', 'Horizontal bars show the spread between the slowest and busiest month within each quarter.'),
    18: t('Satu titik mewakili satu wilayah. Makin ke kanan, makin besar total tahunannya. Makin ke atas, makin berfluktuasi volume bulanannya dibanding rata-rata wilayah itu sendiri.', 'Each dot represents a district. Farther right means higher annual total; higher up means greater monthly fluctuation.'),
    19: t('Diagram wafel membagi proporsi 100 kotak: porsi warna toska mewakili Semester I dan merah mewakili Semester II. Pada skala provinsi, hasilnya terbelah tepat 50:50 karena kedua semester memiliki musim puncak yang setara (Lebaran di Semester I dan Nataru di Semester II). Pilih wilayah di atas untuk melihat disparitas semester tiap kabupaten.', 'The waffle chart splits 100 squares between Semester I (teal) and Semester II (red). At the provincial scale it splits 50:50 because both halves feature balanced peaks (Lebaran in H1 and Year-End in H2). Select a district above to view local semester disparities.'),
    20: t('Membandingkan persentase porsi volume 4 triwulan untuk 5 daerah terbesar. Terlihat jelas kapan tiap daerah mencapai panen wisatawannya (misal Donggala-Parigi memuncak di T2 saat libur Lebaran, sedangkan Palu-Sigi memuncak di T4 saat libur akhir tahun).', 'Compares quarterly percentage share (Q1–Q4) for the top five regions. Shows when each district peaks (e.g. Donggala-Parigi peak in Q2 during Eid, whereas Palu-Sigi peak in Q4 during Year-End).'),
    21: t('Menilai keramaian 4 Triwulan terhadap patokan rata-rata normal (100). Kelopak merah yang mekar melebihi garis 100 menunjukkan musim puncak liburan (Peak Season).', 'Evaluates the four quarters against the normal baseline of 100. Petals blooming past 100 indicate peak holiday seasons.'),
    22: t('Menampilkan lapisan volume lima daerah terbesar dari waktu ke waktu secara bertumpuk hingga membentuk total gabungan.', 'Displays the stacked volume layers of the top five districts over time to show their cumulative contribution.'),
    23: t('Batang 100% menunjukkan perbandingan persentase porsi Semester I vs Semester II yang dicapai oleh tiap kabupaten/kota.', 'The 100% stacked bars show the percentage share of Semester I vs Semester II achieved by each regency/city.'),
    24: t('Bagian busur berwarna toska menunjukkan porsi persentase kabupaten teratas (Kota Palu) terhadap seluruh total pariwisata provinsi.', 'The teal arc shows the percentage share of the top district (Kota Palu) relative to the entire provincial tourism volume.'),
    25: t('Diagram air terjun menghubungkan volume Januari hingga Desember. Hijau = Kenaikan bulanan (+); Merah = Penurunan bulanan (-); Biru Tua = Total realisasi awal (Januari) dan akhir (Desember).', 'Waterfall chart connects January to December. Green = Monthly gain (+); Red = Monthly drop (-); Dark Teal = Baseline January and final December total.'),
    26: t('Sumbu horizontal adalah porsi terhadap provinsi, sumbu vertikal adalah variasi bulanan, dan ukuran gelembung menunjukkan volume bulan puncak wilayah tersebut.', 'X-axis is provincial share, Y-axis is monthly variation, and bubble size reflects the peak-month volume of that region.'),
    27: t('Tingkat Penghunian Kamar (TPK) mengukur persentase kamar hotel bintang yang terisi oleh tamu dari total kamar yang tersedia.', 'Room Occupancy Rate (TPK) measures the percentage of available star-rated hotel rooms occupied by guests.'),
    28: t('Membandingkan jumlah tamu yang menginap di hotel bintang: lapisan bawah untuk tamu domestik dan lapisan atas untuk tamu mancanegara (asing).', 'Compares guests staying in star hotels: lower stack for domestic guests and upper stack for international guests.'),
    29: t('Persentase dihitung dari jumlah tamu asing dibagi seluruh tamu hotel bintang pada bulan publikasi BPS tersebut.', 'Calculates the percentage of foreign guests relative to all guests in star-rated hotels for each reporting month.'),
    30: t('Membandingkan total gabungan tamu hotel bintang antar-bulan rilis resmi BPS untuk melihat tren pemulihan industri perhotelan.', 'Compares total combined hotel guests across official BPS release months to track the hospitality recovery trend.')
}));
</script>

<template>
    <section class="space-y-5">
        <div class="kartu-togean space-y-4 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-heading text-xl font-bold text-tinta">{{ t('Statistik resmi pariwisata 2025', 'Official tourism statistics 2025') }}</h2>
                    <p class="mt-1 text-sm text-tinta-redup">{{ t('Periode Januari–Desember 2025. Cakupan 13 kabupaten/kota Sulawesi Tengah.', 'January–December 2025. Covers 13 districts and cities in Central Sulawesi.') }}</p>
                </div>
                <div class="flex flex-wrap gap-4 text-sm font-semibold text-utama-teks">
                    <a :href="csvUrl" download="pariwisata_resmi_2025.csv" class="underline">{{ t('Unduh CSV', 'Download CSV') }}</a>
                    <a :href="source" target="_blank" rel="noopener noreferrer" class="underline">{{ t('PDF sumber resmi', 'Official source PDF') }}</a>
                </div>
            </div>
            <div role="group" :aria-label="t('Bagian statistik', 'Statistics sections')" class="flex flex-wrap gap-2">
                <button v-for="item in sections" :key="item.id" type="button" :aria-pressed="section === item.id"
                    class="rounded-lg border border-garis px-3 py-2 text-sm font-semibold"
                    :class="section === item.id ? 'bg-utama-muda text-utama-teks' : 'text-tinta hover:bg-permukaan-redup'"
                    @click="section = item.id">{{ item.label }} <span class="ml-1 text-xs opacity-80">{{ item.numbers.length }}</span></button>
            </div>
            <div v-if="section !== 'hotel'" class="flex flex-wrap gap-4">
                <label class="grid gap-1 text-sm font-semibold">{{ t('Indikator', 'Indicator') }}
                    <select v-model="metric" class="rounded-lg border border-garis bg-permukaan px-3 py-2 text-tinta">
                        <option value="perjalanan_wisnus">{{ t('Perjalanan wisnus', 'Domestic trips') }}</option>
                        <option value="kunjungan_wisman">{{ t('Kunjungan wisman', 'International visits') }}</option>
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">{{ t('Wilayah untuk tren bulanan', 'Area for monthly trends') }}
                    <select v-model="region" class="rounded-lg border border-garis bg-permukaan px-3 py-2 text-tinta">
                        <option value="">{{ t('Seluruh Sulawesi Tengah', 'All Central Sulawesi') }}</option>
                        <option v-for="name in regions" :key="name">{{ name }}</option>
                    </select>
                </label>
            </div>
            <p class="text-sm text-tinta-redup">{{ t('Satuan perjalanan wisnus dan kunjungan wisman ditampilkan terpisah. Tanda kosong pada sumber tetap kosong di CSV; agregat menjumlahkan angka yang tersedia.', 'Domestic trips and international visits are shown separately. Source blanks stay blank in the CSV; totals sum published values.') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <KartuChart v-for="chart in visibleCharts" :key="chart.kind" :judul="chart.title" :jenis="chart.kind"
                :nomor="chart.number" :cara-baca="readingGuides[chart.number]"
                :subjudul="unit + ' · ' + ((chart.number <= 3 || chart.local) ? (region || t('Sulawesi Tengah', 'Central Sulawesi')) : t('13 kabupaten/kota', '13 districts and cities'))"
                :insight="chart.insight" :rows="chart.rows" sumber="Dinas Pariwisata Provinsi Sulawesi Tengah · Rekap 2025" :tahun-data="tourismYears">
                <AnalyticMarks v-if="['lollipop', 'diverging', 'waffle'].includes(chart.type)"
                    :mode="chart.type" :rows="chart.rows" :maximum="Math.max(1, ...chart.rows.map(row => Math.abs(row.value)))" />
                <ResponsiveChart v-else :type="chart.type" :height="300" :options="optionsFor(chart)" :series="seriesFor(chart)" />
            </KartuChart>
        </div>
        <div v-if="section === 'hotel'" class="kartu-togean space-y-2 p-5">
            <h2 class="font-heading text-xl font-bold text-tinta">{{ t('Perhotelan resmi', 'Official hotel statistics') }}</h2>
            <p class="text-sm text-tinta-redup">{{ t('Hotel bintang tingkat provinsi, berdasarkan tiga bulan publikasi pilihan. Filter perjalanan di atas berlaku untuk grafik perjalanan.', 'Provincial star-rated hotels for three selected reporting months. The filters above apply to travel charts.') }}</p>
            <div class="flex flex-wrap gap-4 text-sm font-semibold text-utama-teks">
                <a :href="hotelCsvUrl" download="perhotelan_resmi_pilihan.csv" class="underline">{{ t('Unduh CSV hotel', 'Download hotel CSV') }}</a>
                <a v-for="row in hotelRows" :key="row.label" :href="row.sourceUrl" target="_blank" rel="noopener noreferrer" class="underline">BPS {{ row.label }}</a>
            </div>
        </div>
        <div v-if="section === 'hotel'" class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <KartuChart v-for="(chart, index) in hotelCharts" :key="chart.kind" :judul="chart.title" :jenis="chart.kind" :nomor="charts.length + advancedCharts.length + index + 1"
                :cara-baca="readingGuides[27 + index]" :compact="chart.type === 'dot'" :rows="chart.rows" sumber="Badan Pusat Statistik Provinsi Sulawesi Tengah" :tahun-data="hotelYears">
                <AnalyticMarks v-if="chart.type === 'dot'" :mode="chart.type" :rows="chart.rows" :maximum="chart.maximum" suffix="%" />
                <ResponsiveChart v-else :type="chart.type" :height="300" :options="optionsFor(chart)" :series="chart.series" />
            </KartuChart>
        </div>
    </section>
</template>
