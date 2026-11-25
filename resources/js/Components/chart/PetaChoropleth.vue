<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import L from 'leaflet';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    wilayahData: { type: Array, default: () => [] },
    destinasiData: { type: Array, default: () => [] },
    mode: { type: String, default: 'choropleth' }, // 'choropleth' or 'destinasi'
    metricKey: { type: String, default: 'total_pengunjung' },
    proportional: { type: Boolean, default: false },
    height: { type: String, default: '360px' },
});

const emit = defineEmits(['select-item']);
const { t } = useAppContext();
const mapContainer = ref(null);
let mapInstance = null;
let layerGroup = null;
let resizeObserver = null;

// Sequential Teal Scale from DESIGN.md Section 2.4
const getSequentialColor = (ratio) => {
    if (ratio >= 0.8) return '#0A5F67';
    if (ratio >= 0.6) return '#0E7C86';
    if (ratio >= 0.4) return '#1F9AA5';
    if (ratio >= 0.25) return '#6BC5B8';
    if (ratio >= 0.1) return '#B4DEE1';
    return '#E6F4F5';
};

const categoryColors = {
    'Bahari': '#0E7C86',
    'Alam': '#2E8B57',
    'Budaya & Sejarah': '#F26B4F',
    'Religi': '#E0A422',
    'Ikon Kota': '#12344D',
};

const renderLayers = () => {
    if (!mapInstance || !layerGroup) return;
    layerGroup.clearLayers();

    if (props.mode === 'choropleth' && props.wilayahData.length) {
        const vals = props.wilayahData.map((w) => Number(w[props.metricKey] ?? 0));
        const maxVal = Math.max(...vals, 1);

        props.wilayahData.forEach((w) => {
            const val = Number(w[props.metricKey] ?? 0);
            const ratio = val / maxVal;
            if (props.proportional && !(ratio > 0)) {
                return;
            }
            const color = props.proportional ? '#0E7C86' : getSequentialColor(ratio);
            const radius = props.proportional ? Math.sqrt(ratio) * 34 : 14 + Math.round(ratio * 22);

            const circle = L.circleMarker([Number(w.latitude), Number(w.longitude)], {
                radius,
                fillColor: color,
                color: '#12344D',
                weight: 2,
                opacity: 0.95,
                fillOpacity: 0.85,
            });

            const formattedVal = new Intl.NumberFormat('id-ID').format(val);
            circle.bindPopup(`
                <div style="font-family: Inter, sans-serif; min-width: 180px;">
                    <div style="font-size: 11px; color: #556877; font-weight: 600;">KODE BPS: ${w.kode_bps || '-'}</div>
                    <div style="font-size: 14px; font-weight: 700; color: #12344D; margin: 2px 0 6px;">${w.nama_wilayah}</div>
                    <div style="font-size: 12px; color: #0E7C86; font-weight: 700;">
                        ${props.metricKey === 'jumlah_destinasi' ? 'Jumlah Destinasi' : 'Total Kunjungan'}: ${formattedVal}
                    </div>
                </div>
            `);

            circle.on('click', () => emit('select-item', w));
            circle.addTo(layerGroup);
        });
    } else if (props.destinasiData.length) {
        props.destinasiData.forEach((d) => {
            const color = categoryColors[d.kategori] || '#0E7C86';
            const marker = L.circleMarker([Number(d.latitude), Number(d.longitude)], {
                radius: 10,
                fillColor: color,
                color: '#FFFFFF',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.92,
            });

            const hargaFormatted = d.harga_tiket_dewasa == null ? t('Belum tersedia', 'Unavailable') : d.harga_tiket_dewasa > 0
                ? 'Rp ' + new Intl.NumberFormat('id-ID').format(d.harga_tiket_dewasa)
                : 'Gratis';

            marker.bindPopup(`
                <div style="font-family: Inter, sans-serif; min-width: 210px;">
                    <span style="display:inline-block; background:#D5EEF0; color:#0A5F67; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;">
                        ${d.kategori}
                    </span>
                    <div style="font-size: 14px; font-weight: 700; color: #12344D; margin: 4px 0 2px;">${d.nama_destinasi}</div>
                    <div style="font-size: 11px; color: #556877; margin-bottom: 6px;">${d.wilayah?.nama_wilayah || ''}</div>
                    <div style="font-size: 12px; color: #12344D;">
                        <strong>Rating:</strong> ★ ${d.rating_rata_rata} &nbsp;|&nbsp; <strong>Tiket:</strong> ${hargaFormatted}
                    </div>
                    <div style="font-size: 11px; margin-top: 4px; color: #0E7C86; font-weight:600;">
                        Keramaian Hari Ini: ${d.tingkat_keramaian_hari_ini || 'Belum ada data'}
                    </div>
                </div>
            `);

            marker.on('click', () => emit('select-item', d));
            marker.addTo(layerGroup);
        });
    }
};

onMounted(() => {
    if (!mapContainer.value) return;
    mapInstance = L.map(mapContainer.value, {
        center: [-1.15, 121.2],
        zoom: 7,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors | Jelajah Sulteng',
        maxZoom: 18,
    }).addTo(mapInstance);

    layerGroup = L.layerGroup().addTo(mapInstance);
    renderLayers();
    resizeObserver = new ResizeObserver(() => mapInstance?.invalidateSize({ pan: false }));
    resizeObserver.observe(mapContainer.value);
});

watch(
    () => [props.wilayahData, props.destinasiData, props.mode, props.metricKey, props.proportional],
    () => renderLayers(),
    { deep: true }
);

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
    }
});
</script>

<template>
    <div class="relative w-full overflow-hidden rounded-xl border border-[var(--warna-garis)]">
        <div ref="mapContainer" :style="{ height }" class="z-10 w-full"></div>

        <!-- Legenda Peta -->
        <p v-if="mode === 'choropleth' && proportional" class="absolute bottom-3 left-3 z-20 rounded-lg bg-white/95 px-3 py-2 text-xs text-[#12344D] shadow-md">
            {{ t('Luas lingkaran sebanding dengan jumlah kunjungan.', 'Circle area represents the number of visits.') }}
        </p>
        <div
            v-else-if="mode === 'choropleth'"
            class="absolute bottom-3 left-3 z-20 rounded-lg border border-[var(--warna-garis)] bg-white/95 px-3 py-2 text-[11px] text-[#12344D] shadow-md"
        >
            <div class="mb-1 font-bold">{{ t('Skala intensitas wilayah', 'Regional intensity scale') }}</div>
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] text-[#556877]">{{ t('Rendah', 'Low') }}</span>
                <span class="h-3 w-4 rounded-sm bg-[#E6F4F5] border border-gray-300"></span>
                <span class="h-3 w-4 rounded-sm bg-[#B4DEE1]"></span>
                <span class="h-3 w-4 rounded-sm bg-[#6BC5B8]"></span>
                <span class="h-3 w-4 rounded-sm bg-[#1F9AA5]"></span>
                <span class="h-3 w-4 rounded-sm bg-[#0E7C86]"></span>
                <span class="h-3 w-4 rounded-sm bg-[#0A5F67]"></span>
                <span class="text-[10px] text-[#556877]">{{ t('Tinggi', 'High') }}</span>
            </div>
        </div>
        <div
            v-else
            class="absolute bottom-3 left-3 right-3 z-20 flex flex-wrap items-center gap-2 rounded-lg border border-[var(--warna-garis)] bg-white/95 px-3 py-2 text-[11px] text-[#12344D] shadow-md"
        >
            <span v-for="(clr, kat) in categoryColors" :key="kat" class="inline-flex items-center gap-1 font-medium">
                <span class="inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: clr }"></span>
                {{ kat }}
            </span>
        </div>
    </div>
</template>

