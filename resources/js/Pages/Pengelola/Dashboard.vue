<template>
    <AppLayout>
        <section class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header & Destination Selector (if Admin previewing) -->
            <PageHeading :title="destinasi.nama_destinasi" :section="t('Pengelola destinasi', 'Destination manager')" :description="destinasi.wilayah?.nama_wilayah || ''"><template #summary><span class="inline-flex flex-wrap items-center gap-x-4 gap-y-1"><span>{{ destinasi.kategori }}</span><span>{{ t('Jam buka:', 'Opening hours:') }} {{ destinasi.jam_operasional }}</span><span>{{ t('Kapasitas:', 'Capacity:') }} {{ formatNumber(destinasi.kapasitas_harian) }} {{ t('orang', 'visitors') }}</span></span></template><template #actions><div class="flex flex-wrap items-center gap-3">
                    <div v-if="allDestinations.length > 1" class="flex w-full min-w-0 flex-col items-start gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <label class="text-xs font-bold text-[#5A6B7C] dark:text-[#94A3B8]">{{ t('Pilih Destinasi:', 'Switch Spot:') }}</label>
                        <select
                            :class="'w-full sm:max-w-72'" :aria-label="t('Pilih destinasi', 'Choose destination')"
                            :value="destinasi.id"
                            @change="switchDestinasi($event.target.value)"
                            class="px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-bold text-[#12344D] dark:text-[#F1F5F9]"
                        >
                            <option v-for="d in allDestinations" :key="d.id" :value="d.id">
                                {{ d.nama_destinasi }}
                            </option>
                        </select>
                    </div>

                    <Link
                        :href="`/destinasi/${destinasi.slug || destinasi.id}`"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-[#0E7C86] text-[#0E7C86] dark:text-[#28A8B3] text-xs font-bold hover:bg-[#E6F4F5] dark:hover:bg-[#152A3D] transition-colors"
                    >
                        {{ t('Lihat Halaman Publik →', 'View Public Page →') }}
                    </Link>
                </div></template></PageHeading>
<ScrollTabs :active-key="activeTab" :label="t('Modul pengelola', 'Manager sections')" class="workspace-tabs flex items-center gap-2 border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                <button
                    v-for="tb in tabs"
                    :key="tb.id"
                    type="button"
                    @click="activeTab = tb.id"
                    :aria-pressed="activeTab === tb.id"
                    class="px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-colors"
                    :class="activeTab === tb.id
                        ? 'bg-[#0E7C86] text-white shadow-sm'
                        : 'bg-white dark:bg-[#152A3D] text-[#5A6B7C] dark:text-[#94A3B8] hover:text-[#12344D] border border-[#E5DEC9] dark:border-[#243B53]'"
                >
                    {{ tb.label }}
                </button>
            </ScrollTabs>

            <!-- 4 KPI Cards -->
            <div v-show="activeTab === 'analitik' || activeTab === 'operasional'" class="kpi-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="surface-card p-5 border-l-2 border-[#0E7C86]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8]">
                        {{ t('Kunjungan harian terakhir', 'Latest daily visitors') }}
                    </span>
                    <div class="text-2xl font-extrabold text-[#12344D] dark:text-[#F1F5F9] mt-1 tabular-nums">
                        {{ formatNumber(kpi.hari_ini_total) }} <span class="text-xs font-normal text-[#5A6B7C]">/ {{ formatNumber(destinasi.kapasitas_harian) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-tinta-redup">{{ kpi.tanggal_laporan ? formatDate(kpi.tanggal_laporan) : t('Belum ada laporan', 'No report yet') }}</p>
                    <div v-if="kpi.tanggal_laporan" class="mt-2 flex items-center gap-2 text-xs">
                        <span
                            class="px-2 py-0.5 rounded font-bold"
                            :class="{
                                'bg-emerald-100 text-emerald-800': kpi.hari_ini_status === 'Sepi',
                                'bg-amber-100 text-amber-800': kpi.hari_ini_status === 'Sedang',
                                'bg-red-100 text-red-800': kpi.hari_ini_status === 'Ramai',
                            }"
                        >
                            {{ kpi.hari_ini_status }} ({{ kpi.hari_ini_keramaian_persen }}%)
                        </span>
                    </div>
                </div>

                <div class="surface-card p-5 border-l-2 border-[#12344D]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8]">
                        {{ t('Kunjungan bulanan terakhir', 'Latest monthly visitors') }}
                    </span>
                    <div class="text-2xl font-extrabold text-[#12344D] dark:text-[#F1F5F9] mt-1 tabular-nums">
                        {{ formatNumber(kpi.bulan_ini_total) }}
                    </div>
                    <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-2">
                        {{ t('Periode:', 'Period:') }} {{ kpi.periode_bulanan || t('Belum ada laporan', 'No report yet') }}
                    </p>
                </div>

                <div class="surface-card p-5 border-l-2 border-[#1F8A5B]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8]">
                        {{ t('Pendapatan bulanan terakhir', 'Latest monthly revenue') }}
                    </span>
                    <div class="text-2xl font-extrabold text-[#1F8A5B] mt-1 tabular-nums">
                        {{ formatRupiah(kpi.bulan_ini_pendapatan_rp) }}
                    </div>
                    <p class="mt-1 text-xs text-tinta-redup">{{ t('Periode:', 'Period:') }} {{ kpi.periode_bulanan || t('Belum ada laporan', 'No report yet') }}</p>
                    <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-2 tabular-nums"> {{ t("Dewasa:", "Adults:") }} {{ formatRupiah(destinasi.harga_tiket_dewasa) }} • Anak: {{ formatRupiah(destinasi.harga_tiket_anak) }}
                    </p>
                </div>

                <div class="surface-card p-5 border-l-2 border-[#E0A422]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#5A6B7C] dark:text-[#94A3B8]">
                        {{ t('Rating & Ulasan Wisatawan', 'Visitor Rating & Reviews') }}
                    </span>
                    <div class="text-2xl font-extrabold text-[#12344D] dark:text-[#F1F5F9] mt-1 tabular-nums">
                        ★ {{ Number(kpi.rating_rata_rata ?? null).toFixed(2) }} <span class="text-sm font-semibold text-[#5A6B7C]">/ 5.00</span>
                    </div>
                    <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mt-2 tabular-nums">
                        {{ formatNumber(kpi.jumlah_ulasan) }} {{ t('ulasan terverifikasi', 'verified reviews') }}
                    </p>
                </div>
            </div>

            <!-- Navigation Tabs -->
            

            <!-- TAB 1: 6 ANALYTICS CHARTS PENGELOLA (Charts #8, #9, #10, #11, #12, #30) -->
            <div v-show="activeTab === 'analitik'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Chart #8: 30-Day Daily Visitors Line Chart -->
                    <div class="lg:col-span-8">
                        <KartuChart
                            nomor="Chart #8 — Pengelola Destinasi"
                            :judul="t('Bagaimana tren kunjungan harian destinasi dalam 30 hari terakhir?', 'How did daily destination visits trend over the last 30 days?')"
                            :subjudul="t('Garis pengunjung harian (Wisnus + Wisman) terhadap kapasitas harian.', 'Daily visitors (Domestic + Foreign) compared against daily capacity.')"
                            :insight="`${t('Rata-rata kunjungan 30 hari terakhir:', '30-day average attendance:')} ${avg30Days} ${t('orang/hari', 'visitors/day')}.`"
                            :columns="[
                                { key: 'tanggal', label: 'Tanggal' },
                                { key: 'wisnus', label: 'Wisnus', align: 'right' },
                                { key: 'wisman', label: 'Wisman', align: 'right' },
                                { key: 'total_pengunjung', label: 'Total', align: 'right' },
                                { key: 'keramaian_persen', label: 'Keramaian (%)', align: 'right' },
                            ]"
                            :rows="charts.chart_08_harian_30hari_line || []"
                        >
                            <ResponsiveChart
                                type="area"
                                height="310"
                                :options="chart08Options"
                                :series="chart08Series"
                            />
                        </KartuChart>
                    </div>

                    <!-- Chart #10: Komposisi Wisnus vs Wisman Pie Chart -->
                    <div class="lg:col-span-4">
                        <KartuChart
                            nomor="Chart #10 — Pengelola Destinasi"
                            :judul="t('Berapa proporsi Wisatawan Nusantara dibanding Mancanegara?', 'What is the ratio of Domestic vs. International tourists?')"
                            :subjudul="t('Komposisi pengunjung 12 bulan terakhir (Pie Chart).', '12-month visitor origin composition (Pie Chart).')"
                            :insight="`Wisnus: ${formatNumber(charts.chart_10_komposisi_wisatawan_pie?.wisnus || 0)} | Wisman: ${formatNumber(charts.chart_10_komposisi_wisatawan_pie?.wisman || 0)}`"
                            :columns="[
                                { key: 'segmen', label: 'Segmen Wisatawan' },
                                { key: 'jumlah', label: 'Total Pengunjung', align: 'right' },
                            ]"
                            :rows="[
                                { segmen: 'Wisatawan Nusantara (Wisnus)', jumlah: charts.chart_10_komposisi_wisatawan_pie?.wisnus || 0 },
                                { segmen: 'Wisatawan Mancanegara (Wisman)', jumlah: charts.chart_10_komposisi_wisatawan_pie?.wisman || 0 },
                            ]"
                        >
                            <ResponsiveChart
                                type="pie"
                                height="310"
                                :options="chart10Options"
                                :series="chart10Series"
                            />
                        </KartuChart>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Chart #9: Perbandingan Bulan Ini vs Bulan Lalu & Tahun Lalu Grouped Bar -->
                    <div class="lg:col-span-6">
                        <KartuChart
                            nomor="Chart #9 — Pengelola Destinasi"
                            :judul="t('Bagaimana perbandingan kunjungan bulan berjalan terhadap bulan lalu & tahun lalu?', 'How do current monthly visits compare to last month and last year?')"
                            :subjudul="t('Grouped Bar perbandingan MoM (Bulan Lalu) dan YoY (Tahun Lalu) Mei–Oktober.', 'Grouped Bar comparing MoM and YoY performance (May–October).')"
                            :columns="[
                                { key: 'bulan', label: 'Periode' },
                                { key: 'bulan_ini', label: 'Bulan Berjalan (2026)', align: 'right' },
                                { key: 'bulan_lalu', label: 'Bulan Sebelumnya', align: 'right' },
                                { key: 'tahun_lalu', label: 'Bulan Sama 2025', align: 'right' },
                            ]"
                            :rows="charts.chart_09_perbandingan_bulanan_bar || []"
                        >
                            <ResponsiveChart
                                type="bar"
                                height="300"
                                :options="chart09Options"
                                :series="chart09Series"
                            />
                        </KartuChart>
                    </div>

                    <!-- Chart #11: Penjualan Tiket Dewasa vs Anak Stacked Bar -->
                    <div class="lg:col-span-6">
                        <KartuChart
                            nomor="Chart #11 — Pengelola Destinasi"
                            :judul="t('Bagaimana komposisi penjualan E-Tiket Dewasa dan Anak per periode?', 'What is the breakdown of Adult vs. Child E-Ticket sales per period?')"
                            :subjudul="t('Stacked Bar jumlah tiket terjual dari transaksi E-Tiket.', 'Stacked Bar of ticket quantities sold via E-Ticket transactions.')"
                            :columns="[
                                { key: 'periode', label: 'Periode' },
                                { key: 'total_dewasa', label: 'Tiket Dewasa', align: 'right' },
                                { key: 'total_anak', label: 'Tiket Anak', align: 'right' },
                                { key: 'total_pendapatan', label: 'Pendapatan (Rp)', align: 'right' },
                            ]"
                            :rows="charts.chart_11_tiket_dewasa_anak_stacked || []"
                        >
                            <ResponsiveChart
                                type="bar"
                                height="300"
                                :options="chart11Options"
                                :series="chart11Series"
                            />
                        </KartuChart>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Chart #12: Sentimen Ulasan Destinasi Donut Chart -->
                    <div class="lg:col-span-5">
                        <KartuChart
                            nomor="Chart #12 — Pengelola Destinasi"
                            :judul="t('Bagaimana distribusi sentimen ulasan pengunjung di destinasi ini?', 'What is the sentiment distribution of visitor reviews at this spot?')"
                            :subjudul="t('Klasifikasi sentimen Positif, Netral, dan Negatif (Donut Chart).', 'Positive, Neutral, and Negative review sentiment (Donut Chart).')"
                            :columns="[
                                { key: 'label_sentimen', label: 'Sentimen' },
                                { key: 'total', label: 'Jumlah Ulasan', align: 'right' },
                            ]"
                            :rows="charts.chart_12_sentimen_ulasan_donut || []"
                        >
                            <ResponsiveChart
                                type="donut"
                                height="300"
                                :options="chart12Options"
                                :series="chart12Series"
                            />
                        </KartuChart>
                    </div>

                    <!-- Chart #30: Radar 5 Aspek Destinasi vs Rata-Rata Provinsi -->
                    <div class="lg:col-span-7">
                        <KartuChart
                            nomor="Chart #30 — Pengelola Destinasi"
                            :judul="t('Bagaimana profil 5 aspek kualitas destinasi ini dibanding rata-rata Sulawesi Tengah?', 'How does this destination\'s 5-aspect quality profile compare to the provincial average?')"
                            :subjudul="t('Radar perbandingan Pemandangan, Kebersihan, Akses, Fasilitas, dan Nilai Harga (skala 1–5).', 'Radar comparing Scenery, Cleanliness, Access, Facilities, and Price Value (1–5 scale).')"
                            :columns="[
                                { key: 'aspek', label: 'Aspek Penilaian' },
                                { key: 'skor_destinasi', label: destinasi.nama_destinasi, align: 'right' },
                                { key: 'skor_provinsi', label: 'Rata-rata Provinsi', align: 'right' },
                            ]"
                            :rows="radarTableRows"
                        >
                            <ResponsiveChart
                                type="radar"
                                height="310"
                                :options="chart30Options"
                                :series="chart30Series"
                            />
                        </KartuChart>
                    </div>
                </div>
            </div>

            <!-- TAB 2: INPUT KUNJUNGAN HARIAN & IMPOR CSV + VERIFIKASI E-TIKET -->
            <div v-show="activeTab === 'operasional'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Input Kunjungan Harian & Impor CSV -->
                <div class="lg:col-span-6 space-y-6">
                    <form @submit.prevent="submitKunjungan" class="surface-card p-6 space-y-4">
                        <div v-if="Object.keys(kunjunganForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in kunjunganForm.errors" :key="field">{{ message }}</li></ul></div>
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                            {{ t('1. Input Kunjungan Harian & Indikator Keramaian', '1. Daily Visitor Input & Crowd Indicator') }}
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="field-pengelola-dashboard-kunjunganForm-tanggal" class="block text-xs font-bold uppercase text-[#5A6B7C] mb-1">{{ t('Tanggal', 'Date') }} *</label>
                                <input :aria-invalid="Boolean(kunjunganForm.errors.tanggal)" id="field-pengelola-dashboard-kunjunganForm-tanggal"
                                    v-model="kunjunganForm.tanggal"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9]"
                                />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-kunjunganForm-wisnus" class="block text-xs font-bold uppercase text-[#5A6B7C] mb-1">{{ t("Wisnus *", "Domestic visitors *") }}</label>
                                <input :aria-invalid="Boolean(kunjunganForm.errors.wisnus)" id="field-pengelola-dashboard-kunjunganForm-wisnus"
                                    v-model.number="kunjunganForm.wisnus"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-kunjunganForm-wisman" class="block text-xs font-bold uppercase text-[#5A6B7C] mb-1">{{ t("Wisman *", "International visitors *") }}</label>
                                <input :aria-invalid="Boolean(kunjunganForm.errors.wisman)" id="field-pengelola-dashboard-kunjunganForm-wisman"
                                    v-model.number="kunjunganForm.wisman"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-semibold text-[#12344D] dark:text-[#F1F5F9] tabular-nums"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="field-pengelola-dashboard-kunjunganForm-catatan_operasional" class="block text-xs font-bold uppercase text-[#5A6B7C] mb-1">{{ t('Catatan Operasional', 'Operational Notes') }}</label>
                            <input :aria-invalid="Boolean(kunjunganForm.errors.catatan_operasional)" id="field-pengelola-dashboard-kunjunganForm-catatan_operasional"
                                v-model="kunjunganForm.catatan_operasional"
                                type="text"
                                :placeholder="t('Contoh: Cuaca cerah, rombongan sekolah 2 bus...', 'e.g. Clear weather, 2 school tour buses...')"
                                class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs text-[#12344D] dark:text-[#F1F5F9]"
                            />
                        </div>

                        <div class="p-3 rounded-lg bg-[#E6F4F5] dark:bg-[#152A3D] flex items-center justify-between text-xs">
                            <span>{{ t('Total Dihitung Otomatis:', 'Auto-Calculated Total:') }} <strong>{{ kunjunganForm.wisnus + kunjunganForm.wisman }} {{ t("orang", "visitors") }}</strong></span>
                            <span>{{ t("Kapasitas:", "Capacity:") }} <strong>{{ Math.round(((kunjunganForm.wisnus + kunjunganForm.wisman) / Math.max(1, destinasi.kapasitas_harian)) * 100) }}%</strong></span>
                        </div>

                        <button
                            type="submit"
                            :disabled="kunjunganForm.processing"
                            class="w-full py-2.5 px-4 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-xs font-bold transition-colors"
                        >
                            {{ kunjunganForm.processing ? t('Menyimpan...', 'Saving...') : t('Simpan Kunjungan Harian & Sinkronkan ke Data Warehouse', 'Save Daily Visits & Sync to Data Warehouse') }}
                        </button>
                    </form>

                    <!-- CSV Bulk Upload -->
                    <form @submit.prevent="submitCsv" class="surface-card p-6 space-y-4">
                        <h3 class="text-base font-bold text-[#12344D] dark:text-[#F1F5F9]">
                            {{ t('Unggah Massal Kunjungan Harian (Format CSV)', 'Bulk Upload Daily Visits (CSV Format)') }}
                        </h3>
                        <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                            {{ t('Format kolom CSV: tanggal,wisnus,wisman (Contoh: 2026-11-06,145,12)', 'CSV column format: tanggal,wisnus,wisman (e.g. 2026-11-06,145,12)') }}
                        </p>
                        <input
                            type="file"
                            accept=".csv,.txt"
                            @change="csvForm.file_csv = $event.target.files[0]"
                            required
                            class="block w-full text-xs text-[#5A6B7C] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#E6F4F5] file:text-[#0E7C86]"
                        />
                        <button
                            type="submit"
                            :disabled="csvForm.processing || !csvForm.file_csv"
                            class="py-2 px-4 rounded-lg bg-[#12344D] hover:bg-[#0E7C86] disabled:opacity-50 text-white text-xs font-bold transition-colors"
                        >
                            {{ t('Impor File CSV', 'Import CSV File') }}
                        </button>
                    </form>

                    <!-- Recent Daily Visits Table -->
                    <div class="surface-card p-6">
                        <h3 class="text-sm font-bold text-[#12344D] dark:text-[#F1F5F9] mb-3">
                            {{ t('15 Catatan Kunjungan Harian Terbaru', 'Latest 15 Daily Visit Logs') }}
                        </h3>
                        <div class="overflow-x-auto max-h-72">
                            <table class="w-full text-xs">
                                <thead class="bg-[#FAF7F0] dark:bg-[#0D1B2A] text-[#5A6B7C]">
                                    <tr>
                                        <th class="p-2 text-left">{{ t("Tanggal", "Date") }}</th>
                                        <th class="p-2 text-right">{{ t("Wisnus", "Domestic") }}</th>
                                        <th class="p-2 text-right">{{ t("Wisman", "International") }}</th>
                                        <th class="p-2 text-right">{{ t("Total", "Total") }}</th>
                                        <th class="p-2 text-right">{{ t("Keramaian", "Crowd level") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5DEC9] dark:divide-[#243B53] tabular-nums">
                                    <tr v-for="kh in kunjunganTerbaru" :key="kh.id">
                                        <td class="p-2 font-semibold">{{ formatDate(kh.tanggal) }}</td>
                                        <td class="p-2 text-right">{{ formatNumber(kh.wisnus) }}</td>
                                        <td class="p-2 text-right">{{ formatNumber(kh.wisman) }}</td>
                                        <td class="p-2 text-right font-bold">{{ formatNumber(kh.total_pengunjung) }}</td>
                                        <td class="p-2 text-right">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#E6F4F5] text-[#0E7C86]">
                                                {{ kh.tingkat_keramaian }} ({{ kh.keramaian_persen }}%)
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Verifikasi Kode E-Tiket QR -->
                <div class="lg:col-span-6 space-y-6">
                    <form @submit.prevent="submitVerifikasiTiket" class="surface-card p-6 space-y-4">
                        <div v-if="Object.keys(verifikasiForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in verifikasiForm.errors" :key="field">{{ message }}</li></ul></div>
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                            {{ t('2. Loket Check-In & Verifikasi Kode E-Tiket', '2. Check-In Counter & E-Ticket Code Verification') }}
                        </h2>
                        <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                            {{ t('Masukkan kode E-Tiket wisatawan (contoh: TKT-000012) atau klik tombol Check-In cepat pada tabel di bawah.', 'Enter the visitor E-Ticket code (e.g. TKT-000012) or click quick Check-In on the table below.') }}
                        </p>
                        <div class="flex items-center gap-3">
                            <label for="field-pengelola-dashboard-verifikasiForm-kode_tiket" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Kode tiket', 'Ticket code') }}</span><input id="field-pengelola-dashboard-verifikasiForm-kode_tiket" :aria-invalid="Boolean(verifikasiForm.errors.kode_tiket)"
                                v-model="verifikasiForm.kode_tiket"
                                type="text"
                                required
                                placeholder="TKT-000001"
                                class="flex-1 px-3.5 py-2.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] font-mono text-sm font-bold text-[#12344D] dark:text-[#F1F5F9] uppercase"
                            /></label>
                            <button
                                type="submit"
                                :disabled="verifikasiForm.processing"
                                class="px-5 py-2.5 rounded-lg bg-[#1F8A5B] hover:bg-emerald-700 text-white text-xs font-bold transition-colors"
                            >
                                {{ t('Verifikasi Check-In', 'Verify Check-In') }}
                            </button>
                        </div>
                    </form>

                    <!-- Recent Tickets for this Destination -->
                    <div class="surface-card p-6">
                        <h3 class="text-sm font-bold text-[#12344D] dark:text-[#F1F5F9] mb-3">
                            {{ t('Daftar E-Tiket Terbaru untuk Destinasi Ini', 'Recent E-Tickets for This Destination') }}
                        </h3>
                        <div class="overflow-x-auto max-h-96">
                            <table class="w-full text-xs">
                                <thead class="bg-[#FAF7F0] dark:bg-[#0D1B2A] text-[#5A6B7C]">
                                    <tr>
                                        <th class="p-2 text-left">{{ t("Kode Tiket", "Ticket code") }}</th>
                                        <th class="p-2 text-left">{{ t("Tgl Kunjungan", "Visit date") }}</th>
                                        <th class="p-2 text-right">{{ t("Peserta", "Visitors") }}</th>
                                        <th class="p-2 text-right">{{ t("Total Bayar", "Total paid") }}</th>
                                        <th class="p-2 text-center">{{ t("Status / Aksi", "Status / action") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5DEC9] dark:divide-[#243B53] tabular-nums">
                                    <tr v-for="tk in tiketTerbaru" :key="tk.id">
                                        <td class="p-2 font-mono font-bold text-[#12344D] dark:text-[#F1F5F9]">{{ tk.kode_tiket }}</td>
                                        <td class="p-2">{{ formatDate(tk.tanggal_kunjungan) }}</td>
                                        <td class="p-2 text-right">{{ tk.jumlah_dewasa }}D + {{ tk.jumlah_anak }}A</td>
                                        <td class="p-2 text-right font-bold">{{ formatRupiah(tk.total_bayar_rp) }}</td>
                                        <td class="p-2 text-center">
                                            <button
                                                v-if="tk.status === 'Selesai'"
                                                type="button"
                                                @click="quickCheckin(tk.kode_tiket)"
                                                class="px-2.5 py-1 rounded bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-[11px] font-bold"
                                            > {{ t("Check-In", "Check in") }} </button>
                                            <span
                                                v-else
                                                class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                :class="tk.status === 'Sudah Check-In' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                            >
                                                {{ tk.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: BALAS ULASAN WISATAWAN -->
            <div v-show="activeTab === 'ulasan'" class="space-y-4">
                <div class="surface-card p-6">
                    <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] mb-1">
                        {{ t('Manajemen & Tanggapan Ulasan Pengunjung', 'Visitor Reviews & Official Manager Replies') }}
                    </h2>
                    <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8] mb-5">
                        {{ t('Berikan tanggapan resmi atas masukan wisatawan untuk menjaga reputasi dan skor kepuasan destinasi.', 'Respond officially to tourist feedback to maintain reputation and destination satisfaction scores.') }}
                    </p>

                    <div class="space-y-4">
                        <article
                            v-for="ul in ulasanDestinasi"
                            :key="ul.id"
                            class="p-4 rounded-xl bg-[#FAF7F0] dark:bg-[#0D1B2A] border border-[#E5DEC9] dark:border-[#243B53] space-y-2"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <strong class="text-sm text-[#12344D] dark:text-[#F1F5F9]">{{ ul.nama_pengguna }}</strong>
                                    <span class="text-xs text-[#E0A422] font-bold">★ {{ ul.rating }}/5</span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': ul.label_sentimen === 'positif',
                                            'bg-amber-100 text-amber-800': ul.label_sentimen === 'netral',
                                            'bg-red-100 text-red-800': ul.label_sentimen === 'negatif',
                                        }"
                                    >
                                        {{ ul.label_sentimen }}
                                    </span>
                                </div>
                                <span class="text-xs text-[#5A6B7C] tabular-nums">{{ formatDate(ul.tanggal) }}</span>
                            </div>

                            <p class="text-xs text-[#12344D] dark:text-[#F1F5F9]">{{ ul.teks_ulasan }}</p>

                            <div class="text-[11px] text-[#5A6B7C] flex flex-wrap gap-3 tabular-nums">
                                <span>{{ t("Pemandangan:", "Scenery:") }} <strong>{{ ul.skor_pemandangan }}/5</strong></span>
                                <span>{{ t("Kebersihan:", "Cleanliness:") }} <strong>{{ ul.skor_kebersihan }}/5</strong></span>
                                <span>{{ t("Akses:", "Access:") }} <strong>{{ ul.skor_akses }}/5</strong></span>
                                <span>{{ t("Fasilitas:", "Facilities:") }} <strong>{{ ul.skor_fasilitas }}/5</strong></span>
                                <span>{{ t("Harga:", "Value:") }} <strong>{{ ul.skor_harga }}/5</strong></span>
                            </div>

                            <div v-if="ul.balasan_pengelola" class="p-3 rounded-lg bg-[#E6F4F5] dark:bg-[#152A3D] border-l-2 border-[#0E7C86] text-xs">
                                <strong class="text-[#0E7C86] dark:text-[#28A8B3] block">{{ t("Balasan Resmi Pengelola:", "Official manager response:") }}</strong>
                                <span>{{ ul.balasan_pengelola }}</span>
                            </div>

                            <div class="pt-2 flex items-center gap-2">
                                <input
                                    v-model="replyTexts[ul.id]"
                                    type="text"
                                    :placeholder="ul.balasan_pengelola ? 'Perbarui balasan pengelola...' : 'Tulis balasan resmi pengelola...'"
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-white dark:bg-[#152A3D] text-xs text-[#12344D] dark:text-[#F1F5F9]"
                                />
                                <button
                                    type="button"
                                    @click="sendReply(ul.id)"
                                    class="px-3.5 py-1.5 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-xs font-bold"
                                >
                                    {{ t('Kirim Balasan', 'Send Reply') }}
                                </button>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <!-- TAB 4: PEMBARUAN PROFIL, HARGA TIKET & USULAN EVENT -->
            <div v-show="activeTab === 'profil_event'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Ajukan Perubahan Profil & Harga Tiket -->
                <div class="lg:col-span-6 space-y-6">
                    <form @submit.prevent="submitProfilChange" class="surface-card p-6 space-y-4">
                        <div v-if="Object.keys(profilForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in profilForm.errors" :key="field">{{ message }}</li></ul></div>
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                            {{ t('Pembaruan Profil & Pengajuan Tarif Tiket', 'Profile Update & Ticket Tariff Request') }}
                        </h2>
                        <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                            {{ t('Perubahan deskripsi langsung tayang, sedangkan perubahan tarif tiket, jam operasional, dan kapasitas masuk ke antrean persetujuan Admin Dinas.', 'Description updates go live immediately; changes to ticket prices, hours, and capacity are queued for Provincial Admin approval.') }}
                        </p>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="field-pengelola-dashboard-profilForm-harga_tiket_dewasa" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Harga Tiket Dewasa (Rp) *", "Adult ticket price (IDR) *") }}</label>
                                <input :aria-invalid="Boolean(profilForm.errors.harga_tiket_dewasa)" id="field-pengelola-dashboard-profilForm-harga_tiket_dewasa" v-model.number="profilForm.harga_tiket_dewasa" type="number" min="0" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-bold tabular-nums" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-profilForm-harga_tiket_anak" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Harga Tiket Anak (Rp) *", "Child ticket price (IDR) *") }}</label>
                                <input :aria-invalid="Boolean(profilForm.errors.harga_tiket_anak)" id="field-pengelola-dashboard-profilForm-harga_tiket_anak" v-model.number="profilForm.harga_tiket_anak" type="number" min="0" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-bold tabular-nums" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-profilForm-jam_operasional" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Jam Operasional *", "Opening hours *") }}</label>
                                <input :aria-invalid="Boolean(profilForm.errors.jam_operasional)" id="field-pengelola-dashboard-profilForm-jam_operasional" v-model="profilForm.jam_operasional" type="text" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-bold" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-profilForm-kapasitas_harian" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Kapasitas Harian *", "Daily capacity *") }}</label>
                                <input :aria-invalid="Boolean(profilForm.errors.kapasitas_harian)" id="field-pengelola-dashboard-profilForm-kapasitas_harian" v-model.number="profilForm.kapasitas_harian" type="number" min="10" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs font-bold tabular-nums" />
                            </div>
                        </div>

                        <div>
                            <label for="field-pengelola-dashboard-profilForm-deskripsi_id" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Deskripsi Bahasa Indonesia *", "Indonesian description *") }}</label>
                            <textarea :aria-invalid="Boolean(profilForm.errors.deskripsi_id)" id="field-pengelola-dashboard-profilForm-deskripsi_id" v-model="profilForm.deskripsi_id" rows="3" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs"></textarea>
                        </div>

                        <div>
                            <label for="field-pengelola-dashboard-profilForm-deskripsi_en" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Deskripsi Bahasa Inggris *", "English description *") }}</label>
                            <textarea :aria-invalid="Boolean(profilForm.errors.deskripsi_en)" id="field-pengelola-dashboard-profilForm-deskripsi_en" v-model="profilForm.deskripsi_en" rows="3" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs"></textarea>
                        </div>

                        <div>
                            <label for="field-pengelola-dashboard-profilForm-alasan_perubahan" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Alasan Pengajuan Perubahan *", "Reason for change *") }}</label>
                            <input :aria-invalid="Boolean(profilForm.errors.alasan_perubahan)" id="field-pengelola-dashboard-profilForm-alasan_perubahan" v-model="profilForm.alasan_perubahan" type="text" required :placeholder="t('Contoh: Penyesuaian fasilitas dermaga baru & Perda retribusi...', 'Example: New facilities or revised admission regulations?')" class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs" />
                        </div>

                        <button type="submit" :disabled="profilForm.processing" class="w-full py-2.5 px-4 rounded-lg bg-[#0E7C86] hover:bg-[#0A5C64] text-white text-xs font-bold">
                            {{ t('Simpan Deskripsi & Ajukan Perubahan ke Admin Dinas', 'Save Description & Submit Changes for Approval') }}
                        </button>
                    </form>
                </div>

                <!-- Right: Usulan Event Destinasi -->
                <div class="lg:col-span-6 space-y-6">
                    <form @submit.prevent="submitEventProposal" class="surface-card p-6 space-y-4">
                        <div v-if="Object.keys(eventForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in eventForm.errors" :key="field">{{ message }}</li></ul></div>
                        <h2 class="text-lg font-bold text-[#12344D] dark:text-[#F1F5F9] border-b border-[#E5DEC9] dark:border-[#243B53] pb-3">
                            {{ t('Pengajuan Event / Atraksi Baru', 'New Destination Event Proposal') }}
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label for="field-pengelola-dashboard-eventForm-nama_event_id" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Nama Event (ID) *", "Event name (Indonesian) *") }}</label>
                                <input :aria-invalid="Boolean(eventForm.errors.nama_event_id)" id="field-pengelola-dashboard-eventForm-nama_event_id" v-model="eventForm.nama_event_id" type="text" required :placeholder="t('Contoh: Pekan Konservasi Terumbu Karang Togean', 'Example: Togean Coral Reef Conservation Week')" class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-eventForm-tanggal_mulai" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Tanggal Mulai *", "Start date *") }}</label>
                                <input :aria-invalid="Boolean(eventForm.errors.tanggal_mulai)" id="field-pengelola-dashboard-eventForm-tanggal_mulai" v-model="eventForm.tanggal_mulai" type="date" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-eventForm-tanggal_selesai" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Tanggal Selesai *", "End date *") }}</label>
                                <input :aria-invalid="Boolean(eventForm.errors.tanggal_selesai)" id="field-pengelola-dashboard-eventForm-tanggal_selesai" v-model="eventForm.tanggal_selesai" type="date" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs" />
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-eventForm-kategori_event" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Kategori Event *", "Event category *") }}</label>
                                <select :aria-invalid="Boolean(eventForm.errors.kategori_event)" id="field-pengelola-dashboard-eventForm-kategori_event" v-model="eventForm.kategori_event" class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs">
                                    <option value="Festival Bahari">{{ t("Festival Bahari", "Marine festival") }}</option>
                                    <option value="Budaya & Adat">{{ t("Budaya & Adat", "Culture & traditions") }}</option>
                                    <option value="Ekowisata & Konservasi">{{ t("Ekowisata & Konservasi", "Ecotourism & conservation") }}</option>
                                    <option value="Olahraga Wisata">{{ t("Olahraga Wisata", "Sports tourism") }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="field-pengelola-dashboard-eventForm-harga_tiket_rp" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Harga Tiket Event (Rp)", "Event ticket price (IDR)") }}</label>
                                <input :aria-invalid="Boolean(eventForm.errors.harga_tiket_rp)" id="field-pengelola-dashboard-eventForm-harga_tiket_rp" v-model.number="eventForm.harga_tiket_rp" type="number" min="0" class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs tabular-nums" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="field-pengelola-dashboard-eventForm-lokasi_detail" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Lokasi Spesifik *", "Specific location *") }}</label>
                                <input :aria-invalid="Boolean(eventForm.errors.lokasi_detail)" id="field-pengelola-dashboard-eventForm-lokasi_detail" v-model="eventForm.lokasi_detail" type="text" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="field-pengelola-dashboard-eventForm-deskripsi_id" class="block text-xs font-bold text-[#5A6B7C] mb-1">{{ t("Deskripsi Event *", "Event description *") }}</label>
                                <textarea :aria-invalid="Boolean(eventForm.errors.deskripsi_id)" id="field-pengelola-dashboard-eventForm-deskripsi_id" v-model="eventForm.deskripsi_id" rows="3" required class="w-full px-3 py-2 rounded-lg border border-[#E5DEC9] dark:border-[#243B53] bg-[#FAF7F0] dark:bg-[#0D1B2A] text-xs"></textarea>
                            </div>
                        </div>
                        <button type="submit" :disabled="eventForm.processing" class="w-full py-2.5 px-4 rounded-lg bg-[#12344D] hover:bg-[#0E7C86] text-white text-xs font-bold">
                            {{ t('Ajukan Event ke Kalender Wisata Provinsi', 'Submit Event Proposal to Provincial Calendar') }}
                        </button>
                    </form>

                    <!-- Riwayat Pengajuan Perubahan -->
                    <div class="surface-card p-6">
                        <h3 class="text-sm font-bold text-[#12344D] dark:text-[#F1F5F9] mb-3">
                            {{ t('Riwayat Pengajuan Perubahan Profil & Tarif', 'Profile & Tariff Change Request History') }}
                        </h3>
                        <div class="space-y-2">
                            <div v-for="pr in riwayatPerubahan" :key="pr.id" class="p-3 rounded-lg bg-[#FAF7F0] dark:bg-[#0D1B2A] border border-[#E5DEC9] dark:border-[#243B53] text-xs flex items-center justify-between">
                                <div>
                                    <strong class="block text-[#12344D] dark:text-[#F1F5F9]">{{ pr.jenis_perubahan }}</strong>
                                    <span class="text-[#5A6B7C]">{{ pr.alasan_perubahan }}</span>
                                </div>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                    :class="{
                                        'bg-amber-100 text-amber-800': pr.status === 'menunggu',
                                        'bg-emerald-100 text-emerald-800': pr.status === 'disetujui',
                                        'bg-red-100 text-red-800': pr.status === 'ditolak',
                                    }"
                                >
                                    {{ pr.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import ResponsiveChart from '@/Components/chart/ResponsiveChart.vue';
import ScrollTabs from '@/Components/ui/ScrollTabs.vue';
import PageHeading from '@/Components/ui/PageHeading.vue';
import { ref, reactive, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
import AppLayout from '../../Layouts/AppLayout.vue';
import KartuChart from '../../Components/chart/KartuChart.vue';
import { useAppContext, getLocalDate } from '../../composables/useAppContext';

const props = defineProps({
    destinasi: { type: Object, required: true },
    allDestinations: { type: Array, default: () => [] },
    kpi: { type: Object, required: true },
    charts: { type: Object, required: true },
    kunjunganTerbaru: { type: Array, default: () => [] },
    tiketTerbaru: { type: Array, default: () => [] },
    ulasanDestinasi: { type: Array, default: () => [] },
    riwayatPerubahan: { type: Array, default: () => [] },
});

const { t, formatNumber, formatRupiah, formatDate } = useAppContext();

const activeTab = ref('analitik');
const tabs = computed(() => [
    { id: 'analitik', label: t('Analitik destinasi', 'Destination analytics') },
    { id: 'operasional', label: t('Kunjungan & tiket', 'Visits & tickets') },
    { id: 'ulasan', label: t('Ulasan wisatawan', 'Visitor reviews') },
    { id: 'profil_event', label: t('Profil & agenda', 'Profile & events') },
]);

function switchDestinasi(destId) {
    router.get('/pengelola/dashboard', { destinasi_id: destId }, { preserveState: true });
}

// Forms
const kunjunganForm = useForm({
    destinasi_id: props.destinasi.id,
    tanggal: getLocalDate(),
    wisnus: 115,
    wisman: 14,
    catatan_operasional: 'Cuaca cerah, operasional normal',
});

function submitKunjungan() {
    kunjunganForm.destinasi_id = props.destinasi.id;
    kunjunganForm.post('/pengelola/kunjungan-harian', { preserveScroll: true });
}

const csvForm = useForm({
    destinasi_id: props.destinasi.id,
    file_csv: null,
});

function submitCsv() {
    csvForm.destinasi_id = props.destinasi.id;
    csvForm.post('/pengelola/kunjungan-csv', { preserveScroll: true });
}

const verifikasiForm = useForm({
    kode_tiket: '',
});

function submitVerifikasiTiket() {
    verifikasiForm.post('/pengelola/verifikasi-tiket', {
        preserveScroll: true,
        onSuccess: () => { verifikasiForm.kode_tiket = ''; },
    });
}

function quickCheckin(kode) {
    verifikasiForm.kode_tiket = kode;
    submitVerifikasiTiket();
}

const replyTexts = reactive({});
function sendReply(ulasanId) {
    const txt = replyTexts[ulasanId];
    if (!txt) return;
    router.post(`/pengelola/ulasan/${ulasanId}/balas`, { balasan_pengelola: txt }, { preserveScroll: true });
}

const profilForm = useForm({
    destinasi_id: props.destinasi.id,
    harga_tiket_dewasa: props.destinasi.harga_tiket_dewasa,
    harga_tiket_anak: props.destinasi.harga_tiket_anak,
    jam_operasional: props.destinasi.jam_operasional,
    kapasitas_harian: props.destinasi.kapasitas_harian,
    deskripsi_id: props.destinasi.deskripsi_id,
    deskripsi_en: props.destinasi.deskripsi_en,
    alasan_perubahan: '',
});

function submitProfilChange() {
    profilForm.destinasi_id = props.destinasi.id;
    profilForm.post('/pengelola/perubahan-profil', { preserveScroll: true });
}

const eventForm = useForm({
    destinasi_id: props.destinasi.id,
    nama_event_id: '',
    nama_event_en: '',
    tanggal_mulai: '2026-12-05',
    tanggal_selesai: '2026-12-07',
    lokasi_detail: props.destinasi.nama_destinasi,
    kategori_event: 'Festival Bahari',
    harga_tiket_rp: 0,
    deskripsi_id: '',
});

function submitEventProposal() {
    eventForm.destinasi_id = props.destinasi.id;
    eventForm.post('/pengelola/ajukan-event', {
        preserveScroll: true,
        onSuccess: () => {
            eventForm.nama_event_id = '';
            eventForm.deskripsi_id = '';
        },
    });
}

// CHART CONFIGURATIONS (Charts #8, #9, #10, #11, #12, #30)
const c8List = computed(() => props.charts.chart_08_harian_30hari_line || []);
const avg30Days = computed(() => {
    if (!c8List.value.length) return 0;
    const sum = c8List.value.reduce((acc, r) => acc + Number(r.total_pengunjung || 0), 0);
    return Math.round(sum / c8List.value.length);
});

const chart08Series = computed(() => [
    { name: 'Total Pengunjung', data: c8List.value.map((r) => Number(r.total_pengunjung || 0)) },
    { name: 'Wisatawan Mancanegara', data: c8List.value.map((r) => Number(r.wisman || 0)) },
]);

const chart08Options = computed(() => ({
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    colors: ['#0E7C86', '#F26B4F'],
    stroke: { curve: 'smooth', width: [3, 2] },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: c8List.value.map((r) => String(r.tanggal).slice(5)),
        labels: { style: { fontSize: '10px' } },
    },
    yaxis: { labels: { formatter: (v) => Math.round(v) } },
    legend: { position: 'top' },
}));

const c9List = computed(() => props.charts.chart_09_perbandingan_bulanan_bar || []);
const chart09Series = computed(() => [
    { name: 'Bulan Tercatat', data: c9List.value.map((r) => Number(r.bulan_ini || 0)) },
    { name: 'Bulan Lalu', data: c9List.value.map((r) => r.bulan_lalu == null ? null : Number(r.bulan_lalu)) },
    { name: 'Bulan Sama Tahun Lalu', data: c9List.value.map((r) => r.tahun_lalu == null ? null : Number(r.tahun_lalu)) },
]);
const chart09Options = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    colors: ['#0E7C86', '#28A8B3', '#12344D'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '65%' } },
    dataLabels: { enabled: false },
    xaxis: { categories: c9List.value.map((r) => r.bulan) },
    legend: { position: 'top' },
}));

const chart10Series = computed(() => [
    Number(props.charts.chart_10_komposisi_wisatawan_pie?.wisnus || 0),
    Number(props.charts.chart_10_komposisi_wisatawan_pie?.wisman || 0),
]);
const chart10Options = computed(() => ({
    chart: { type: 'pie', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    labels: ['Wisatawan Nusantara (Wisnus)', 'Wisatawan Mancanegara (Wisman)'],
    colors: ['#0E7C86', '#F26B4F'],
    stroke: { width: 3, colors: ['var(--warna-permukaan)'] },
    dataLabels: {
        enabled: true,
        minAngleToShowLabel: 18,
        style: { fontSize: '11px', fontFamily: 'JetBrains Mono, monospace', fontWeight: 700, colors: ['#FFFFFF'] },
        dropShadow: { enabled: false },
    },
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        fontSize: '12px',
        fontWeight: 600,
        formatter: (seriesName, opts) => {
            const val = Number(opts.w.globals.series[opts.seriesIndex] || 0);
            const total = opts.w.globals.seriesTotals.reduce((a, b) => a + Number(b || 0), 0) || 1;
            const pct = ((val / total) * 100).toFixed(1).replace('.', ',');
            return `${seriesName}: ${val.toLocaleString('id-ID')} (${pct}%)`;
        },
    },
}));

const c11List = computed(() => (props.charts.chart_11_tiket_dewasa_anak_stacked || []).slice(-12));
const chart11Series = computed(() => [
    { name: 'Tiket Dewasa', data: c11List.value.map((r) => Number(r.total_dewasa || 0)) },
    { name: 'Tiket Anak', data: c11List.value.map((r) => Number(r.total_anak || 0)) },
]);
const chart11Options = computed(() => ({
    chart: { type: 'bar', stacked: true, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    colors: ['#12344D', '#E0A422'],
    plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
    dataLabels: { enabled: false },
    xaxis: { categories: c11List.value.map((r) => r.periode) },
    legend: { position: 'top' },
}));

const c12List = computed(() => props.charts.chart_12_sentimen_ulasan_donut || []);
const chart12Series = computed(() => c12List.value.map((r) => Number(r.total || 0)));
const chart12Options = computed(() => ({
    chart: { type: 'donut', fontFamily: 'Plus Jakarta Sans, sans-serif' },
    labels: c12List.value.map((r) => String(r.label_sentimen || '').toUpperCase()),
    colors: ['#2E8B57', '#E0A422', '#C2492E'],
    stroke: { width: 2, colors: ['var(--warna-permukaan)'] },
    dataLabels: {
        enabled: true,
        formatter: (val) => `${Number(val).toFixed(1)}%`,
        style: {
            fontSize: '10px',
            fontFamily: 'JetBrains Mono, monospace',
            fontWeight: 700,
            colors: ['#FFFFFF'],
        },
        dropShadow: { enabled: false },
    },
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        fontSize: '12px',
        fontWeight: 600,
        formatter: (seriesName, opts) => {
            const val = Number(opts.w.globals.series[opts.seriesIndex] || 0);
            const total = opts.w.globals.seriesTotals.reduce((a, b) => a + Number(b || 0), 0) || 1;
            const pct = ((val / total) * 100).toFixed(1).replace('.', ',');
            return `${seriesName}: ${val.toLocaleString('id-ID')} (${pct}%)`;
        },
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: 8,
                minAngleToShowLabel: 5,
            },
            donut: {
                size: '54%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '11px', fontWeight: 700, offsetY: -5 },
                    value: { show: true, fontSize: '19px', fontWeight: 800, offsetY: 5, formatter: (val) => `${Number(val).toLocaleString('id-ID')}` },
                    total: {
                        show: true,
                        showAlways: true,
                        label: 'Total Ulasan',
                        fontSize: '11px',
                        fontWeight: 700,
                        formatter: (w) => `${w.globals.seriesTotals.reduce((a, b) => a + Number(b || 0), 0).toLocaleString('id-ID')}`,
                    },
                },
            },
        },
    },
}));

const myAsp = computed(() => props.charts.chart_30_aspek_radar_pengelola?.destinasi || {});
const prAsp = computed(() => props.charts.chart_30_aspek_radar_pengelola?.provinsi || {});

const radarTableRows = computed(() => [
    { aspek: 'Pemandangan', skor_destinasi: myAsp.value.pemandangan ?? null, skor_provinsi: prAsp.value.pemandangan ?? null },
    { aspek: 'Kebersihan', skor_destinasi: myAsp.value.kebersihan ?? null, skor_provinsi: prAsp.value.kebersihan ?? null },
    { aspek: 'Aksesibilitas', skor_destinasi: myAsp.value.akses ?? null, skor_provinsi: prAsp.value.akses ?? null },
    { aspek: 'Fasilitas', skor_destinasi: myAsp.value.fasilitas ?? null, skor_provinsi: prAsp.value.fasilitas ?? null },
    { aspek: 'Nilai Harga', skor_destinasi: myAsp.value.harga ?? null, skor_provinsi: prAsp.value.harga ?? null },
]);

const chart30Series = computed(() => [
    {
        name: props.destinasi.nama_destinasi,
        data: [
            (myAsp.value.pemandangan == null ? null : Number(myAsp.value.pemandangan)),
            (myAsp.value.kebersihan == null ? null : Number(myAsp.value.kebersihan)),
            (myAsp.value.akses == null ? null : Number(myAsp.value.akses)),
            (myAsp.value.fasilitas == null ? null : Number(myAsp.value.fasilitas)),
            (myAsp.value.harga == null ? null : Number(myAsp.value.harga)),
        ],
    },
    {
        name: 'Rata-rata Provinsi Sulteng',
        data: [
            (prAsp.value.pemandangan == null ? null : Number(prAsp.value.pemandangan)),
            (prAsp.value.kebersihan == null ? null : Number(prAsp.value.kebersihan)),
            (prAsp.value.akses == null ? null : Number(prAsp.value.akses)),
            (prAsp.value.fasilitas == null ? null : Number(prAsp.value.fasilitas)),
            (prAsp.value.harga == null ? null : Number(prAsp.value.harga)),
        ],
    },
]);

const chart30Options = computed(() => ({
    chart: { type: 'radar', toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    colors: ['#0E7C86', '#F26B4F'],
    dataLabels: { enabled: false },
    xaxis: { categories: ['Pemandangan', 'Kebersihan', 'Aksesibilitas', 'Fasilitas', 'Nilai Harga'] },
    yaxis: { show: false, min: 0, max: 5, tickAmount: 5 },
    stroke: { width: 2 },
    fill: { opacity: 0.2 },
    markers: { size: 4 },
}));
</script>

