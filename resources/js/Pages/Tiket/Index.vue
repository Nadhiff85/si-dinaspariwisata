<template>
    <AppLayout>
        <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Header -->
            <PageHeading :title="t('Pemesanan &amp; Riwayat E-Tiket Wisatawan', 'E-Ticket Booking &amp; History')" :section="t('Tiket saya', 'My tickets')" :description="t('Pesan tiket masuk, lihat riwayat, dan siapkan kode tiket untuk kunjungan Anda.', 'Book admission, review your tickets, and prepare your ticket code for arrival.')" />

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:gap-8">
                <!-- Left Column: New Ticket Booking Form -->
                <div class="xl:col-span-5">
                    <form @submit.prevent="submitBooking" class="surface-card p-6 space-y-4 sticky top-24">
                        <div v-if="Object.keys(bookingForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in bookingForm.errors" :key="field">{{ message }}</li></ul></div>
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] flex items-center gap-2 border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                            <QrCode class="w-5 h-5 text-[#0E7C86]" />
                            {{ t('Pesan E-Tiket Baru', 'Book New E-Ticket') }}
                        </h2>

                        <div>
                            <label for="field-tiket-index-bookingForm-destinasi_id" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                {{ t('Pilih Destinasi Wisata', 'Select Destination') }} *
                            </label>
                            <select :aria-invalid="Boolean(bookingForm.errors.destinasi_id)" id="field-tiket-index-bookingForm-destinasi_id"
                                v-model.number="bookingForm.destinasi_id"
                                required
                                class="w-full px-3 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9]"
                            >
                                <option v-for="d in destinasiOptions" :key="d.id" :value="d.id">
                                    {{ d.nama_destinasi }} ({{ d.wilayah?.nama_wilayah }}) — {{ formatRupiah(d.harga_tiket_dewasa) }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="field-tiket-index-bookingForm-tanggal_kunjungan" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                {{ t('Tanggal Kunjungan', 'Visit Date') }} *
                            </label>
                            <input :aria-invalid="Boolean(bookingForm.errors.tanggal_kunjungan)" id="field-tiket-index-bookingForm-tanggal_kunjungan"
                                v-model="bookingForm.tanggal_kunjungan"
                                type="date"
                                required
                                class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9]"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="field-tiket-index-bookingForm-jumlah_dewasa" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                    {{ t('Dewasa', 'Adults') }} *
                                </label>
                                <input :aria-invalid="Boolean(bookingForm.errors.jumlah_dewasa)" id="field-tiket-index-bookingForm-jumlah_dewasa"
                                    v-model.number="bookingForm.jumlah_dewasa"
                                    type="number"
                                    min="1"
                                    max="50"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                />
                            </div>
                            <div>
                                <label for="field-tiket-index-bookingForm-jumlah_anak" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                    {{ t('Anak-Anak', 'Children') }}
                                </label>
                                <input :aria-invalid="Boolean(bookingForm.errors.jumlah_anak)" id="field-tiket-index-bookingForm-jumlah_anak"
                                    v-model.number="bookingForm.jumlah_anak"
                                    type="number"
                                    min="0"
                                    max="50"
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="field-tiket-index-bookingForm-metode_bayar" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                    {{ t('Metode Pembayaran', 'Payment Method') }} *
                                </label>
                                <select :aria-invalid="Boolean(bookingForm.errors.metode_bayar)" id="field-tiket-index-bookingForm-metode_bayar"
                                    v-model="bookingForm.metode_bayar"
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9]"
                                >
                                    <option value="QRIS">{{ t("QRIS Nusantara", "QRIS") }}</option>
                                    <option value="E-Wallet">{{ t("E-Wallet", "E-wallet") }}</option>
                                    <option value="Transfer Bank">{{ t("Transfer Bank", "Bank transfer") }}</option>
                                    <option value="Bayar di Loket">{{ t("Bayar di Loket", "Pay at the counter") }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="field-tiket-index-bookingForm-asal_pemesan" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                    {{ t('Kategori Wisatawan', 'Visitor Origin') }}
                                </label>
                                <select :aria-invalid="Boolean(bookingForm.errors.asal_pemesan)" id="field-tiket-index-bookingForm-asal_pemesan"
                                    v-model="bookingForm.asal_pemesan"
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9]"
                                >
                                    <option value="Wisnus">{{ t("Wisatawan Nusantara", "Domestic traveller") }}</option>
                                    <option value="Wisman">{{ t("International Tourist", "International traveller") }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#E6F4F5] dark:bg-[#152A3D] border border-[#0E7C86]/30">
                            <div class="flex items-center justify-between text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                                <span>{{ t('Harga Dewasa', 'Adult Price') }}</span>
                                <span class="tabular-nums">{{ formatRupiah(selectedDestination?.harga_tiket_dewasa || 0) }} × {{ bookingForm.jumlah_dewasa }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-1">
                                <span>{{ t('Harga Anak', 'Child Price') }}</span>
                                <span class="tabular-nums">{{ formatRupiah(selectedDestination?.harga_tiket_anak || 0) }} × {{ bookingForm.jumlah_anak }}</span>
                            </div>
                            <div class="flex items-center justify-between text-base font-extrabold text-[#12344D] dark:text-[#F1F5F9] mt-2 pt-2 border-t border-[#0E7C86]/20 tabular-nums">
                                <span>{{ t('Total Bayar', 'Total Amount') }}</span>
                                <span class="text-[#0E7C86] dark:text-[#28A8B3]">{{ formatRupiah(estimatedTotal) }}</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="bookingForm.processing"
                            class="w-full py-2.5 px-4 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-sm font-bold transition-colors shadow-sm"
                        >
                            {{ bookingForm.processing ? t('Memproses...', 'Processing...') : t('Pesan Tiket Sekarang', 'Book Ticket Now') }}
                        </button>
                    </form>
                </div>

                <!-- Right Column: E-Ticket List -->
                <div class="xl:col-span-7 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9]">
                            {{ t('Daftar E-Tiket Anda', 'Your Issued E-Tickets') }} ({{ filteredTickets.length }})
                        </h2>
                        <ScrollTabs :active-key="statusFilter" :label="t('Status tiket', 'Ticket status')" class="workspace-tabs flex items-center gap-2">
                            <button
                                v-for="st in ['SEMUA', 'Selesai', 'Sudah Check-In', 'Dibatalkan']"
                                :key="st"
                                type="button"
                                @click="statusFilter = st"
                                :aria-pressed="statusFilter === st"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                :class="statusFilter === st
                                    ? 'bg-[#0E7C86] text-white'
                                    : 'bg-white dark:bg-[#152A3D] text-[#5A6B7C] border border-[#E5DEC9] dark:border-[#243B53]'"
                            >
                                {{ st === 'SEMUA' ? t('Semua', 'All') : st === 'Selesai' ? t('Selesai', 'Completed') : st === 'Sudah Check-In' ? t('Sudah check-in', 'Checked in') : t('Dibatalkan', 'Cancelled') }}
                            </button>
                        </ScrollTabs>
                    </div>

                    <div v-if="filteredTickets.length === 0" class="surface-card p-10 text-center">
                        <Ticket class="w-10 h-10 text-[#0E7C86] mx-auto mb-2 opacity-80" />
                        <p class="text-sm font-bold text-[#12344D] dark:text-[#F1F5F9]">
                            {{ t('Tidak ada E-Tiket pada filter status ini.', 'No E-Tickets found for this status filter.') }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        <article
                            v-for="tkt in filteredTickets"
                            :key="tkt.id"
                            class="ticket-card surface-card p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Visual Simulated QR Code Box -->
                                <div class="ticket-symbol w-20 h-20 rounded-xl bg-white p-2 border border-garis flex flex-col items-center justify-center shrink-0">
                                    <Ticket class="w-11 h-11 text-[#12344D]" :aria-label="t('Gunakan kode tiket untuk verifikasi petugas', 'Use the ticket code for staff verification')" />
                                    <span class="text-[9px] font-mono font-bold text-[#12344D] mt-0.5 tracking-tighter">
                                        {{ tkt.kode_tiket }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-xs font-extrabold px-2 py-0.5 rounded bg-[#12344D] text-white">
                                            {{ tkt.kode_tiket }}
                                        </span>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="{
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': tkt.status === 'Selesai',
                                                'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300': tkt.status === 'Sudah Check-In',
                                                'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300': tkt.status === 'Dibatalkan',
                                            }"
                                        >
                                            {{ tkt.status }}
                                        </span>
                                        <span class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                                            • Via {{ tkt.metode_bayar }} ({{ tkt.asal_pemesan }})
                                        </span>
                                    </div>

                                    <Link
                                        :href="`/destinasi/${tkt.destinasi?.slug || tkt.destinasi_id}`"
                                        class="text-base font-bold text-[#12344D] dark:text-[#F1F5F9] hover:text-[#0E7C86] mt-1.5 block"
                                    >
                                        {{ tkt.destinasi?.nama_destinasi || 'Destinasi Wisata' }}
                                    </Link>

                                    <div class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 tabular-nums">
                                        <span><strong>{{ t('Kunjungan:', 'Visit Date:') }}</strong> {{ formatDate(tkt.tanggal_kunjungan) }}</span>
                                        <span><strong>{{ t('Peserta:', 'Visitors:') }}</strong> {{ tkt.jumlah_dewasa }} {{ t('Dewasa', 'Adults') }}, {{ tkt.jumlah_anak }} {{ t('Anak', 'Children') }}</span>
                                        <span><strong>{{ t('Dipesan:', 'Booked:') }}</strong> {{ formatDate(tkt.tanggal_pesan) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0 border-[#E5DEC9] dark:border-[#243B53]">
                                <div class="text-left sm:text-right">
                                    <span class="text-[11px] text-[#5A6B7C] dark:text-[#94A3B8] block">{{ t('Total Pembayaran', 'Total Paid') }}</span>
                                    <strong class="text-lg font-extrabold text-[#0E7C86] dark:text-[#28A8B3] tabular-nums">
                                        {{ formatRupiah(tkt.total_bayar_rp) }}
                                    </strong>
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <button
                                        v-if="tkt.status === 'Selesai'"
                                        type="button"
                                        @click="cancelTicket(tkt.id, tkt.kode_tiket)"
                                        class="px-3 py-1 rounded-lg border border-red-300 text-[#DC2626] hover:bg-red-50 text-xs font-semibold transition-colors"
                                    >
                                        {{ t('Batalkan Tiket', 'Cancel Ticket') }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import ScrollTabs from '@/Components/ui/ScrollTabs.vue';
import PageHeading from '@/Components/ui/PageHeading.vue';
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { Ticket } from 'lucide-vue-next';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useAppContext, getLocalDate } from '../../composables/useAppContext';

const props = defineProps({
    tiketList: { type: Array, default: () => [] },
    destinasiOptions: { type: Array, default: () => [] },
});

const { t, formatRupiah, formatDate } = useAppContext();

const statusFilter = ref('SEMUA');

const bookingForm = useForm({
    destinasi_id: props.destinasiOptions[0]?.id || 1,
    tanggal_kunjungan: getLocalDate(),
    jumlah_dewasa: 2,
    jumlah_anak: 0,
    metode_bayar: 'QRIS',
    asal_pemesan: 'Wisnus',
});

const selectedDestination = computed(() => {
    return props.destinasiOptions.find((d) => d.id === bookingForm.destinasi_id) || props.destinasiOptions[0];
});

const estimatedTotal = computed(() => {
    const d = selectedDestination.value;
    if (!d) return 0;
    return (Number(d.harga_tiket_dewasa || 0) * Number(bookingForm.jumlah_dewasa || 1)) +
           (Number(d.harga_tiket_anak || 0) * Number(bookingForm.jumlah_anak || 0));
});

const filteredTickets = computed(() => {
    if (statusFilter.value === 'SEMUA') return props.tiketList;
    return props.tiketList.filter((tk) => tk.status === statusFilter.value);
});

function submitBooking() {
    bookingForm.post('/tiket/pesan', { preserveScroll: true });
}

function cancelTicket(id, kode) {
    if (!confirm(`Apakah Anda yakin ingin membatalkan tiket ${kode}?`)) return;
    router.post(`/tiket/${id}/batal`, {}, { preserveScroll: true });
}
</script>

