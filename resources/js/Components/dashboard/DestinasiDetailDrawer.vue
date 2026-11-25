<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue';
import {
    X, MapPin, Clock, Users, Ticket, ShieldCheck, BarChart3,
    Building2, Pencil, ExternalLink, Sparkles
} from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';
import PhotoCredit from '@/Components/destinasi/PhotoCredit.vue';

const props = defineProps({
    destinasi: { type: Object, default: null },
    pengelola: { type: Object, default: null },
    skorInfo: { type: Object, default: null },
});

const emit = defineEmits(['close', 'focus-charts', 'edit-master']);
const { t, formatNumber, formatRupiah } = useAppContext();
const drawerDialog = ref(null);
let previousOverflow = '';
watch(() => props.destinasi, async (destination) => {
    await nextTick();
    if (destination && !drawerDialog.value?.open) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        drawerDialog.value?.showModal();
    } else if (!destination) {
        drawerDialog.value?.close();
        document.body.style.overflow = previousOverflow;
    }
}, { immediate: true });
onBeforeUnmount(() => { document.body.style.overflow = previousOverflow; });

</script>

<template>
    <Teleport to="body">
        <dialog
            ref="drawerDialog"
            :aria-label="`${t('Ringkasan', 'Overview')} ${destinasi?.nama_destinasi || ''}`"
            @cancel.prevent="emit('close')"
            class="fixed inset-0 m-0 h-dvh max-h-none w-screen max-w-none border-0 bg-black/50 p-0 text-tinta backdrop-blur-xs open:flex open:justify-end"
            @click.self="emit('close')"
        >
            <aside
                v-if="destinasi"
                class="flex h-full w-full max-w-md flex-col justify-between border-l border-garis bg-permukaan shadow-2xl muncul-halus"
            >
                <!-- Bagian Atas & Isi Gulir -->
                <div class="flex-1 overflow-y-auto gulir-tipis">
                    <!-- Sampul Foto Destinasi -->
                    <div class="relative h-52 w-full overflow-hidden bg-navy">
                        <img
                            :src="destinasi.foto_utama"
                            :alt="destinasi.nama_destinasi"
                            :style="{ objectPosition: destinasi.foto_attribution?.position || 'center' }"
                            class="h-full w-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#081723]/90 via-[#081723]/35 to-transparent"></div>

                        <button
                            type="button"
                            @click="emit('close')"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-black/45 text-white hover:bg-black/70 transition cursor-pointer"
                            :aria-label="t('Tutup panel', 'Close panel')"
                        >
                            <X class="h-4 w-4" />
                        </button>

                        <div class="absolute bottom-4 left-5 right-5 text-white">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="rounded-md bg-utama px-2 py-0.5 font-mono text-[10.5px] font-bold">
                                    OBJEK #{{ destinasi.id }}
                                </span>
                                <span class="rounded-md bg-white/20 px-2.5 py-0.5 text-[11px] font-semibold backdrop-blur-xs">
                                    {{ destinasi.kategori }}
                                </span>
                            </div>
                            <h3 class="font-heading text-lg font-extrabold leading-snug">
                                {{ destinasi.nama_destinasi }}
                            </h3>
                            <p class="mt-0.5 flex items-center gap-1 text-xs text-white/85">
                                <MapPin class="h-3.5 w-3.5 text-hijau-laut shrink-0" />
                                <span>{{ destinasi.wilayah?.nama_wilayah || 'Sulawesi Tengah' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">
                        <PhotoCredit :photo="destinasi.foto_attribution" class="text-tinta-redup" />
                        <!-- Skor Komposit (Bila Tersedia) -->
                        <div
                            v-if="skorInfo"
                            class="rounded-xl border border-garis bg-permukaan-redup p-4 flex items-center justify-between gap-3"
                        >
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-tinta-redup">
                                    Skor Unggulan Komposit
                                </div>
                                <div class="mt-0.5 flex items-baseline gap-2">
                                    <span class="font-heading text-2xl font-extrabold text-utama-teks tabular-nums">
                                        {{ Number(skorInfo.skor_total || 0).toFixed(1) }}
                                    </span>
                                    <span class="text-xs text-tinta-redup">/ 100</span>
                                </div>
                                <span class="mt-1 inline-block rounded-md bg-utama-muda px-2 py-0.5 text-[10.5px] font-bold text-utama-teks">
                                    {{ skorInfo.kuadran || t('Belum diklasifikasikan', 'Not classified yet') }}
                                </span>
                            </div>
                            <div class="text-right text-xs space-y-1 tabular-nums">
                                <div class="text-tinta-redup">
                                    Kunjungan 12 Bln:
                                    <strong class="text-tinta">{{ formatNumber(skorInfo.total_kunjungan_12bln || 0) }}</strong>
                                </div>
                                <div class="text-tinta-redup">
                                    Rating Kepuasan:
                                    <strong class="text-emas-teks">★ {{ skorInfo.rating_rata_rata == null ? t('Belum dinilai', 'Not rated') : Number(skorInfo.rating_rata_rata).toFixed(2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Tarif & Kapasitas -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-garis p-3.5">
                                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-tinta-redup">
                                    <Ticket class="h-3.5 w-3.5 text-utama" />
                                    <span>Tarif Tiket Dewasa</span>
                                </div>
                                <div class="mt-1 font-heading text-base font-extrabold text-tinta tabular-nums">
                                    {{ Number(destinasi.harga_tiket_dewasa || 0) > 0 ? formatRupiah(destinasi.harga_tiket_dewasa) : 'Bebas Tiket (Gratis)' }}
                                </div>
                                <div class="text-[11px] text-tinta-redup tabular-nums">
                                    {{ Number(destinasi.harga_tiket_anak || 0) > 0 ? 'Anak: ' + formatRupiah(destinasi.harga_tiket_anak) : 'Akses Publik / Tanpa Retribusi' }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-garis p-3.5">
                                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-tinta-redup">
                                    <Users class="h-3.5 w-3.5 text-utama" />
                                    <span>Kapasitas Harian</span>
                                </div>
                                <div class="mt-1 font-heading text-base font-extrabold text-tinta tabular-nums">
                                    {{ formatNumber(destinasi.kapasitas_harian) }} org
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-tinta-redup">
                                    <Clock class="h-3 w-3" />
                                    <span>{{ destinasi.jam_operasional }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Akun Pengelola Khusus Destinasi Ini -->
                        <div class="rounded-xl border border-utama/35 bg-utama-muda/35 p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-utama-teks">
                                    <ShieldCheck class="h-4 w-4" />
                                    Akun Pengelola Objek Wisata Ini
                                </span>
                                <span class="rounded-full bg-sukses/15 px-2 py-0.5 text-[10px] font-bold text-sukses">
                                    Aktif
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-tinta">
                                {{ pengelola?.name || `Pengelola ${destinasi.nama_destinasi}` }}
                            </div>
                            <div class="font-mono text-[11.5px] font-medium text-utama-teks select-all">
                                {{ pengelola?.email || `pengelola.destinasi${destinasi.id}@pariwisata-sulteng.test` }}
                            </div>
                            <div class="text-[11px] text-tinta-redup">
                                Kata sandi standar: <code class="font-mono font-semibold text-tinta">password</code>
                            </div>
                        </div>

                        <!-- Deskripsi Singkat -->
                        <div v-if="destinasi.deskripsi_id" class="space-y-1">
                            <div class="text-xs font-bold text-tinta">Deskripsi Daya Tarik Wisata</div>
                            <p class="text-xs leading-relaxed text-tinta-redup">
                                {{ destinasi.deskripsi_id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Kendali Akun Utama di Bawah Drawer -->
                <div class="border-t border-garis bg-permukaan-redup p-4 space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="emit('focus-charts', destinasi.id)"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-utama px-3 py-2.5 text-xs font-bold text-white hover:bg-utama-gelap transition cursor-pointer"
                        >
                            <BarChart3 class="h-3.5 w-3.5" />
                            <span>Lihat 6 Chart Objek</span>
                        </button>

                        <Link
                            :href="`/pengelola/dashboard?destinasi_id=${destinasi.id}`"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-navy px-3 py-2.5 text-xs font-bold text-white hover:bg-utama transition"
                        >
                            <Building2 class="h-3.5 w-3.5" />
                            <span>Panel Pengelola →</span>
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="emit('edit-master', destinasi)"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-garis bg-permukaan px-3 py-2 text-xs font-semibold text-tinta hover:border-utama transition cursor-pointer"
                        >
                            <Pencil class="h-3.5 w-3.5 text-utama" />
                            <span>Edit Data & Tarif</span>
                        </button>

                        <Link
                            :href="`/destinasi/${destinasi.slug || destinasi.id}`"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-garis bg-permukaan px-3 py-2 text-xs font-semibold text-tinta hover:border-utama transition"
                        >
                            <ExternalLink class="h-3.5 w-3.5 text-utama" />
                            <span>Halaman Publik</span>
                        </Link>
                    </div>
                </div>
            </aside>
        </dialog>
    </Teleport>
</template>

