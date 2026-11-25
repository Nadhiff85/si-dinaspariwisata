<template>
    <AppLayout>
        <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Header -->
            <PageHeading :title="t('Rencanakan perjalanan Anda', 'Plan your journey')" :section="t('Itinerari', 'Itineraries')" :description="t('Susun rute harian, hitung estimasi tiket, dan simpan rencana perjalanan Anda.', 'Build a daily route, estimate admission costs, and save your travel plan.')">
<template #actions><button
                    type="button"
                    @click="resetForm()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-sm font-semibold transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4" />
                    {{ t('Buat Itinerari Baru', 'Create New Itinerary') }}
                </button></template>
</PageHeading>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:gap-8">
                <!-- Left Column: Saved Itineraries List -->
                <div class="xl:col-span-5 space-y-5">
                    <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] flex items-center justify-between">
                        <span>{{ t('Rencana Perjalanan Tersimpan', 'Saved Trip Plans') }} ({{ itineraries.length }})</span>
                    </h2>

                    <div v-if="itineraries.length === 0" class="surface-card p-8 text-center">
                        <CalendarRange class="w-10 h-10 text-[#0E7C86] mx-auto mb-3 opacity-80" />
                        <p class="text-sm font-semibold text-[#12344D] dark:text-[#F1F5F9]">
                            {{ t('Belum ada rencana perjalanan tersimpan.', 'No saved trip itineraries yet.') }}
                        </p>
                        <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-1">
                            {{ t('Gunakan formulir di sebelah kanan untuk menyusun itinerari baru.', 'Use the builder on the right to create your first itinerary.') }}
                        </p>
                    </div>

                    <article
                        v-for="itin in itineraries"
                        :key="itin.id"
                        class="surface-card p-5 transition-all"
                        :class="form.id === itin.id ? 'ring-2 ring-[#0E7C86]' : ''"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#E6F4F5] dark:bg-[#0D1B2A] text-[#0E7C86] dark:text-[#28A8B3] text-xs font-bold">
                                    {{ itin.jumlah_hari }} {{ t('Hari', 'Days') }} • {{ itin.jumlah_dewasa }} {{ t('Dewasa', 'Adults') }}<span v-if="itin.jumlah_anak > 0">, {{ itin.jumlah_anak }} {{ t('Anak', 'Children') }}</span>
                                </span>
                                <h3 class="text-base font-bold text-[#12344D] dark:text-[#F1F5F9] mt-2">
                                    {{ itin.judul_perjalanan }}
                                </h3>
                                <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-0.5 tabular-nums">
                                    {{ formatDate(itin.tanggal_mulai) }} — {{ formatDate(itin.tanggal_selesai) }}
                                </p>
                            </div>
                            <div class="shrink-0 whitespace-nowrap text-right">
                                <span class="text-[11px] text-[#5A6B7C] dark:text-[#94A3B8] block">{{ t('Estimasi Tiket', 'Est. Tickets') }}</span>
                                <strong class="text-base font-extrabold text-[#0E7C86] dark:text-[#28A8B3] tabular-nums">
                                    {{ formatRupiah(itin.estimasi_biaya_tiket_rp) }}
                                </strong>
                            </div>
                        </div>

                        <p v-if="itin.catatan" class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-3 bg-[#FAF7F0] dark:bg-[#0D1B2A] p-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53]">
                            {{ itin.catatan }}
                        </p>

                        <!-- Timeline Preview of Items -->
                        <div class="mt-4 space-y-2 border-t border-[#E5DEC9] dark:border-[#243B53] pt-3">
                            <div
                                v-for="item in itin.items"
                                :key="item.id"
                                class="flex items-center justify-between text-xs py-1"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="px-1.5 py-0.5 rounded bg-[#12344D] text-white font-bold text-[10px] shrink-0">
                                        H-{{ item.hari_ke }} • {{ item.jam_mulai }}
                                    </span>
                                    <span class="font-semibold text-[#12344D] dark:text-[#F1F5F9] truncate">
                                        {{ item.destinasi?.nama_destinasi }}
                                    </span>
                                </div>
                                <span class="text-[#5A6B7C] dark:text-[#94A3B8] font-medium tabular-nums shrink-0 ml-2">
                                    {{ formatRupiah(item.estimasi_biaya_rp) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 pt-3 border-t border-[#E5DEC9] dark:border-[#243B53] flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="loadItineraryToEdit(itin)"
                                    class="px-3 py-1.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] hover:border-[#0E7C86] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] transition-colors"
                                >
                                    {{ t('Edit Rute', 'Edit Route') }}
                                </button>
                                <a
                                    :href="`/itinerari/${itin.id}/pdf`"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#0E7C86] text-[#0E7C86] dark:text-[#28A8B3] hover:bg-[#E6F4F5] dark:hover:bg-[#0D1B2A] text-xs font-semibold transition-colors"
                                >
                                    <FileDown class="w-3.5 h-3.5" />
                                    {{ t('Unduh PDF', 'Export PDF') }}
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="bookAllTickets(itin.id)"
                                    :disabled="isBookingTickets"
                                    :aria-busy="isBookingTickets"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-xs font-semibold transition-colors"
                                >
                                    <Ticket class="w-3.5 h-3.5" />
                                    {{ isBookingTickets ? t('Memproses tiket…', 'Processing tickets…') : t('Pesan Semua Tiket', 'Book All Tickets') }}
                                </button>
                                <button
                                    type="button"
                                    @click="removeItinerary(itin.id)"
                                    class="p-1.5 rounded-lg text-[#DC2626] hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                    :title="t('Hapus Itinerari', 'Delete Itinerary')"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </article>

                    <!-- Quick Add from Wishlist -->
                    <div v-if="wishlistDestinations && wishlistDestinations.length > 0" class="surface-card p-5">
                        <h3 class="text-sm font-bold text-[#12344D] dark:text-[#F1F5F9] mb-2">
                            {{ t('Tambah Cepat dari Wishlist Anda', 'Quick Add from Your Wishlist') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="wDest in wishlistDestinations"
                                :key="wDest.id"
                                type="button"
                                @click="addDestinationToForm(wDest.id)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#FAF7F0] dark:bg-[#0D1B2A] border border-[#E5DEC9] dark:border-[#243B53] hover:border-[#0E7C86] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5 text-[#0E7C86]" />
                                {{ wDest.nama_destinasi }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Itinerary Form Builder -->
                <div class="xl:col-span-7">
                    <form @submit.prevent="submitItinerary" class="surface-card p-6 sm:p-8 space-y-6">
                        <div v-if="Object.keys(form.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in form.errors" :key="field">{{ message }}</li></ul></div>
                        <div class="flex items-center justify-between border-b border-[#E5DEC9] dark:border-[#243B53] pb-4">
                            <div>
                                <h2 class="text-xl font-bold text-[#12344D] dark:text-[#F1F5F9]">
                                    {{ form.id ? t('Edit Rencana Perjalanan', 'Edit Trip Itinerary') : t('Formulir Penyusun Itinerari Baru', 'New Trip Itinerary Builder') }}
                                </h2>
                                <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-0.5">
                                    {{ t('Pilih destinasi per hari, atur jam kunjungan, dan lihat estimasi total biaya tiket secara langsung.', 'Select destinations per day, set visit times, and view real-time ticket cost estimates.') }}
                                </p>
                            </div>
                            <span v-if="form.id" class="px-2.5 py-1 rounded bg-amber-100 text-amber-800 text-xs font-bold"> {{ t("Mode Edit #", "Editing #") }}{{ form.id }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="field-itinerari-index-form-judul_perjalanan" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                    {{ t('Judul Rencana Perjalanan', 'Trip Title') }} *
                                </label>
                                <input :aria-invalid="Boolean(form.errors.judul_perjalanan)" id="field-itinerari-index-form-judul_perjalanan"
                                    v-model="form.judul_perjalanan"
                                    type="text"
                                    required
                                    :placeholder="t('Contoh: Eksplorasi Bahari Togean & Lembah Bada 4 Hari', 'e.g. 4-Day Togean Marine & Bada Valley Exploration')"
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9]"
                                />
                            </div>

                            <div>
                                <label for="field-itinerari-index-form-tanggal_mulai" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                    {{ t('Tanggal Mulai', 'Start Date') }} *
                                </label>
                                <input :aria-invalid="Boolean(form.errors.tanggal_mulai)" id="field-itinerari-index-form-tanggal_mulai"
                                    v-model="form.tanggal_mulai"
                                    type="date"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9]"
                                />
                            </div>

                            <div>
                                <label for="field-itinerari-index-form-tanggal_selesai" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                    {{ t('Tanggal Selesai', 'End Date') }} *
                                </label>
                                <input :aria-invalid="Boolean(form.errors.tanggal_selesai)" id="field-itinerari-index-form-tanggal_selesai"
                                    v-model="form.tanggal_selesai"
                                    type="date"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9]"
                                />
                            </div>

                            <div>
                                <label for="field-itinerari-index-form-jumlah_hari" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                    {{ t('Durasi (Hari)', 'Duration (Days)') }} *
                                </label>
                                <input :aria-invalid="Boolean(form.errors.jumlah_hari)" id="field-itinerari-index-form-jumlah_hari"
                                    v-model.number="form.jumlah_hari"
                                    type="number"
                                    min="1"
                                    max="7"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="field-itinerari-index-form-jumlah_dewasa" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                        {{ t('Peserta Dewasa', 'Adults') }} *
                                    </label>
                                    <input :aria-invalid="Boolean(form.errors.jumlah_dewasa)" id="field-itinerari-index-form-jumlah_dewasa"
                                        v-model.number="form.jumlah_dewasa"
                                        type="number"
                                        min="1"
                                        max="50"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                    />
                                </div>
                                <div>
                                    <label for="field-itinerari-index-form-jumlah_anak" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                        {{ t('Anak-Anak', 'Children') }}
                                    </label>
                                    <input :aria-invalid="Boolean(form.errors.jumlah_anak)" id="field-itinerari-index-form-jumlah_anak"
                                        v-model.number="form.jumlah_anak"
                                        type="number"
                                        min="0"
                                        max="50"
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                    />
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="field-itinerari-index-form-catatan" class="block text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8] mb-1.5">
                                    {{ t('Catatan Perjalanan / Logistik', 'Trip / Logistics Notes') }}
                                </label>
                                <textarea :aria-invalid="Boolean(form.errors.catatan)" id="field-itinerari-index-form-catatan"
                                    v-model="form.catatan"
                                    rows="2"
                                    :placeholder="t('Contoh: Sewa mobil dari Bandara Mutiara SIS Al-Jufri Palu, bawa perlengkapan snorkeling.', 'e.g. Car rental from Palu Airport, bring snorkeling gear.')"
                                    class="w-full px-3.5 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-sm text-[#12344D] dark:text-[#F1F5F9]"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Itinerary Destination Items -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#12344D] dark:text-[#F1F5F9]">
                                    {{ t('Daftar Destinasi per Hari', 'Destinations Schedule by Day') }} ({{ form.items.length }})
                                </h3>
                                <button
                                    type="button"
                                    @click="addItemRow()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#E6F4F5] dark:bg-[#0D1B2A] text-[#0E7C86] dark:text-[#28A8B3] text-xs font-bold hover:bg-[#0E7C86] hover:text-white transition-colors"
                                >
                                    <Plus class="w-3.5 h-3.5" />
                                    {{ t('Tambah Titik Kunjungan', 'Add Stop') }}
                                </button>
                            </div>

                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="p-4 rounded-xl bg-[#FAF7F0] dark:bg-[#0D1B2A] border border-[#E5DEC9] dark:border-[#243B53] space-y-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-[#0E7C86] dark:text-[#28A8B3]">
                                        #{{ idx + 1 }} — {{ t('Titik Kunjungan', 'Destination Stop') }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            v-if="idx > 0"
                                            type="button"
                                            @click="moveItem(idx, -1)" :aria-label="t('Pindahkan ke atas', 'Move up')"
                                            class="px-2 py-0.5 rounded border border-[#E5DEC9] dark:border-[#243B53] text-xs text-[#5A6B7C] hover:text-[#12344D]"
                                        >
                                            ↑
                                        </button>
                                        <button
                                            v-if="idx < form.items.length - 1"
                                            type="button"
                                            @click="moveItem(idx, 1)" :aria-label="t('Pindahkan ke bawah', 'Move down')"
                                            class="px-2 py-0.5 rounded border border-[#E5DEC9] dark:border-[#243B53] text-xs text-[#5A6B7C] hover:text-[#12344D]"
                                        >
                                            ↓
                                        </button>
                                        <button
                                            v-if="form.items.length > 1"
                                            type="button"
                                            @click="removeItemRow(idx)" :aria-label="t('Hapus destinasi', 'Remove destination')"
                                            class="p-1 text-[#DC2626] hover:bg-red-50 rounded"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <div class="sm:col-span-6">
                                        <label class="block text-[11px] font-semibold text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                            {{ t('Pilih Destinasi', 'Select Destination') }}
                                        </label>
                                        <select
                                            v-model.number="item.destinasi_id" :aria-label="t('Destinasi', 'Destination')"
                                            required
                                            class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9]"
                                        >
                                            <option v-for="d in allDestinations" :key="d.id" :value="d.id">
                                                {{ d.nama_destinasi }} ({{ d.wilayah?.nama_wilayah }}) — {{ formatRupiah(d.harga_tiket_dewasa) }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                            {{ t('Hari Ke-', 'Day #') }}
                                        </label>
                                        <input
                                            v-model.number="item.hari_ke" :aria-label="t('Hari kunjungan', 'Visit day')"
                                            type="number"
                                            min="1"
                                            :max="form.jumlah_hari"
                                            required
                                            class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                            {{ t('Jam Mulai', 'Start Time') }}
                                        </label>
                                        <input
                                            v-model="item.jam_mulai" :aria-label="t('Jam mulai', 'Start time')"
                                            type="time"
                                            required
                                            class="w-full px-2.5 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-[#5A6B7C] dark:text-[#94A3B8] mb-1">
                                            {{ t('Durasi (Mnt)', 'Duration (m)') }}
                                        </label>
                                        <input
                                            v-model.number="item.durasi_menit" :aria-label="t('Durasi dalam menit', 'Duration in minutes')"
                                            type="number"
                                            step="30"
                                            min="30"
                                            class="w-full px-2.5 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                        />
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                                    <input
                                        v-model="item.catatan_item"
                                        type="text"
                                        :placeholder="t('Catatan aktivitas di destinasi ini (opsional)...', 'Activity note for this stop (optional)...')"
                                        class="flex-1 px-3 py-1.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs text-[#12344D] dark:text-[#F1F5F9]"
                                    />
                                    <span class="text-xs font-bold text-[#0E7C86] dark:text-[#28A8B3] tabular-nums shrink-0">
                                        {{ t('Subtotal Tiket:', 'Ticket Subtotal:') }} {{ formatRupiah(calcItemCost(item.destinasi_id)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Cost Banner & Submit -->
                        <div class="p-4 rounded-xl bg-[#E6F4F5] dark:bg-[#152A3D] border border-[#0E7C86]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#0E7C86] dark:text-[#28A8B3] block">
                                    {{ t('Total Estimasi Biaya Tiket Masuk', 'Total Estimated Admission Ticket Cost') }}
                                </span>
                                <span class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                                    {{ form.items.length }} {{ t('destinasi', 'destinations') }} × ({{ form.jumlah_dewasa }} {{ t('Dewasa', 'Adults') }}, {{ form.jumlah_anak }} {{ t('Anak', 'Children') }})
                                </span>
                            </div>
                            <div class="text-2xl font-extrabold text-[#12344D] dark:text-[#F1F5F9] tabular-nums">
                                {{ formatRupiah(totalEstimatedCost) }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button
                                v-if="form.id"
                                type="button"
                                @click="resetForm()"
                                class="px-4 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] text-xs font-semibold text-[#5A6B7C]"
                            >
                                {{ t('Batal Edit', 'Cancel Edit') }}
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] disabled:opacity-50 text-white text-sm font-bold transition-colors shadow-sm"
                            >
                                <Save class="w-4 h-4" />
                                {{ form.processing ? t('Menyimpan...', 'Saving...') : (form.id ? t('Perbarui Itinerari', 'Update Itinerary') : t('Simpan Rencana Perjalanan', 'Save Trip Itinerary')) }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import { computed, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { CalendarRange, Plus, Trash2, FileDown, Ticket, Save } from 'lucide-vue-next';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useAppContext, getLocalDate } from '../../composables/useAppContext';

const props = defineProps({
    itineraries: { type: Array, default: () => [] },
    wishlistDestinations: { type: Array, default: () => [] },
    allDestinations: { type: Array, default: () => [] },
});

const { t, formatRupiah, formatDate } = useAppContext();

const defaultDestId = props.allDestinations[0]?.id || 1;
const isBookingTickets = ref(false);

const form = useForm({
    id: null,
    judul_perjalanan: '',
    tanggal_mulai: getLocalDate(),
    tanggal_selesai: getLocalDate(2),
    jumlah_hari: 3,
    jumlah_dewasa: 2,
    jumlah_anak: 0,
    catatan: '',
    items: [
        {
            destinasi_id: defaultDestId,
            hari_ke: 1,
            urutan: 1,
            jam_mulai: '08:30',
            durasi_menit: 180,
            catatan_item: 'Kunjungan pagi saat cuaca cerah & keramaian sepi',
        },
    ],
});

const destMap = computed(() => {
    const map = {};
    for (const d of props.allDestinations) {
        map[d.id] = d;
    }
    return map;
});

function calcItemCost(destId) {
    const d = destMap.value[destId];
    if (!d) return 0;
    return (Number(d.harga_tiket_dewasa || 0) * Number(form.jumlah_dewasa || 1)) +
           (Number(d.harga_tiket_anak || 0) * Number(form.jumlah_anak || 0));
}

const totalEstimatedCost = computed(() => {
    return form.items.reduce((acc, it) => acc + calcItemCost(it.destinasi_id), 0);
});

function addItemRow() {
    const nextDest = props.allDestinations[form.items.length % props.allDestinations.length]?.id || defaultDestId;
    form.items.push({
        destinasi_id: nextDest,
        hari_ke: Math.min(form.jumlah_hari, form.items.length + 1),
        urutan: form.items.length + 1,
        jam_mulai: '10:00',
        durasi_menit: 120,
        catatan_item: '',
    });
}

function addDestinationToForm(destId) {
    form.items.push({
        destinasi_id: destId,
        hari_ke: 1,
        urutan: form.items.length + 1,
        jam_mulai: '09:00',
        durasi_menit: 150,
        catatan_item: 'Ditambahkan dari daftar Wishlist',
    });
}

function removeItemRow(idx) {
    form.items.splice(idx, 1);
    form.items.forEach((it, i) => { it.urutan = i + 1; });
}

function moveItem(idx, direction) {
    const target = idx + direction;
    if (target < 0 || target >= form.items.length) return;
    const temp = form.items[idx];
    form.items[idx] = form.items[target];
    form.items[target] = temp;
    form.items.forEach((it, i) => { it.urutan = i + 1; });
}

function loadItineraryToEdit(itin) {
    form.id = itin.id;
    form.judul_perjalanan = itin.judul_perjalanan;
    form.tanggal_mulai = String(itin.tanggal_mulai).slice(0, 10);
    form.tanggal_selesai = String(itin.tanggal_selesai).slice(0, 10);
    form.jumlah_hari = itin.jumlah_hari;
    form.jumlah_dewasa = itin.jumlah_dewasa;
    form.jumlah_anak = itin.jumlah_anak;
    form.catatan = itin.catatan || '';
    form.items = (itin.items || []).map((it, idx) => ({
        destinasi_id: it.destinasi_id,
        hari_ke: it.hari_ke,
        urutan: it.urutan || idx + 1,
        jam_mulai: it.jam_mulai || '09:00',
        durasi_menit: it.durasi_menit || 120,
        catatan_item: it.catatan_item || '',
    }));
}

function resetForm() {
    form.id = null;
    form.judul_perjalanan = '';
    form.tanggal_mulai = getLocalDate();
    form.tanggal_selesai = getLocalDate(2);
    form.jumlah_hari = 3;
    form.jumlah_dewasa = 2;
    form.jumlah_anak = 0;
    form.catatan = '';
    form.items = [
        {
            destinasi_id: defaultDestId,
            hari_ke: 1,
            urutan: 1,
            jam_mulai: '08:30',
            durasi_menit: 180,
            catatan_item: '',
        },
    ];
}

function submitItinerary() {
    form.items.forEach((it, i) => { it.urutan = i + 1; });
    form.post('/itinerari', {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    });
}

function removeItinerary(id) {
    if (!confirm('Hapus rencana perjalanan ini?')) return;
    router.delete(`/itinerari/${id}`, { preserveScroll: true });
}

function bookAllTickets(id) {
    if (isBookingTickets.value) return;
    isBookingTickets.value = true;
    router.post(`/itinerari/${id}/pesan-semua`, {}, {
        preserveScroll: true,
        onFinish: () => { isBookingTickets.value = false; },
    });
}
</script>

