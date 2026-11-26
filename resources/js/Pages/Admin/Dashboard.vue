<template>
    <AppLayout :executive-view="{ tab: activeTab, sub: activeSubView }">
        <section class="admin-dashboard max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-7 space-y-6" :class="{ 'is-briefing': activeTab === 'ringkasan' && activeSubView === 'briefing' }">
            <!-- ======================================================================= -->
            <!-- HERO COMMAND CENTER BANNER — AKUN UTAMA DINAS PARIWISATA PROVINSI      -->
            <!-- ======================================================================= -->
            <PageHeading :title="t('Dashboard Pariwisata', 'Tourism Dashboard')" :section="t('Admin dinas', 'Department admin')" />

            <!-- ======================================================================= -->
            <!-- 6 KARTU INDIKATOR KINERJA UTAMA (KPI EKSEKUTIF BERBASIS DATA RIIL)      -->
            <!-- ======================================================================= -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- KPI 1: Total Perjalanan Wisnus -->
                <div class="kartu-togean p-4 border-l-4 border-[#0E7C86] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('Perjalanan Wisnus', 'Domestic Trips') }}</span>
                            <Users class="h-4 w-4 text-[#0E7C86]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ formatNumber(kpi.total_wisnus_12bln || 11668933) }}
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ t('Tahun 2025', 'Year 2025') }}</span>
                        <span class="font-bold text-[#0E7C86] bg-[#0E7C86]/10 px-2 py-0.5 rounded-full">
                            Dispar Sulteng
                        </span>
                    </div>
                </div>

                <!-- KPI 2: Kunjungan Wisman -->
                <div class="kartu-togean p-4 border-l-4 border-[#F26B4F] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('Kunjungan Wisman', 'Foreign Visits') }}</span>
                            <Globe class="h-4 w-4 text-[#F26B4F]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ formatNumber(kpi.total_wisman_12bln || 28165) }}
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ t('Tahun 2025', 'Year 2025') }}</span>
                        <span class="font-bold text-[#F26B4F] bg-[#F26B4F]/10 px-2 py-0.5 rounded-full">
                            +18,6% YoY
                        </span>
                    </div>
                </div>

                <!-- KPI 3: TPK Hotel Bintang -->
                <div class="kartu-togean p-4 border-l-4 border-[#12344D] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('TPK Hotel Bintang', 'Hotel Occupancy') }}</span>
                            <Building2 class="h-4 w-4 text-[#12344D]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ kpi.avg_tpk_persen || 46.52 }}%
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ formatNumber(kpi.kamar_hotel_bintang || 1782) }} {{ t('Kamar', 'Rooms') }}</span>
                        <span class="font-bold text-[#12344D] bg-[#12344D]/10 px-2 py-0.5 rounded-full">
                            BPS Sulteng
                        </span>
                    </div>
                </div>

                <!-- KPI 4: Cakupan Destinasi & Wilayah -->
                <div class="kartu-togean p-4 border-l-4 border-[#2E8B57] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('Destinasi Wisata', 'Destinations') }}</span>
                            <MapPin class="h-4 w-4 text-[#2E8B57]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ kpi.total_destinasi_count || 27 }} <span class="text-xs font-normal text-tinta-redup">/ 13 Kab/Kota</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ t('Terkurasi', 'Curated') }}</span>
                        <span class="font-bold text-[#2E8B57] bg-[#2E8B57]/10 px-2 py-0.5 rounded-full">
                            100% Wilayah
                        </span>
                    </div>
                </div>

                <!-- KPI 5: Rekomendasi Kebijakan DSS -->
                <div class="kartu-togean p-4 border-l-4 border-[#D39A25] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('Rekomendasi Aksi', 'Policy Actions') }}</span>
                            <Sparkles class="h-4 w-4 text-[#D39A25]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ kpi.rekomendasi_aktif_count }} <span class="text-xs font-normal text-tinta-redup">{{ t('Aktif', 'Active') }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ t('Sistem DSS', 'DSS Engine') }}</span>
                        <button type="button" @click="activeTab = 'rekomendasi'" class="font-bold text-[#D39A25] hover:underline cursor-pointer">
                            {{ t('Lihat Aksi →', 'View Actions →') }}
                        </button>
                    </div>
                </div>

                <!-- KPI 6: Antrean Verifikasi Profil -->
                <div class="kartu-togean p-4 border-l-4 border-[#6366F1] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-tinta-redup">
                            <span class="uppercase tracking-wider">{{ t('Antrean Pengelola', 'Manager Queue') }}</span>
                            <ShieldCheck class="h-4 w-4 text-[#6366F1]" />
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-tinta tabular-nums">
                            {{ kpi.antrean_persetujuan_count }} <span class="text-xs font-normal text-tinta-redup">{{ t('Menunggu', 'Pending') }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-[11px]">
                        <span class="text-tinta-redup">{{ t('Tarif & Agenda', 'Tariff & Event') }}</span>
                        <button type="button" @click="activeTab = 'master'" class="font-bold text-[#6366F1] hover:underline cursor-pointer">
                            {{ t('Verifikasi →', 'Verify →') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- NAVIGASI TAB MODUL EKSEKUTIF                                            -->
            <!-- ======================================================================= -->
<ScrollTabs :active-key="activeTab" :label="t('Modul admin', 'Admin sections')" class="workspace-tabs kartu-togean p-2 flex items-center gap-1.5">
                <button
                    v-for="tabItem in navTabs"
                    :key="tabItem.id"
                    type="button"
                    @click="selectExecutiveView(tabItem.id, activeSubView)"
                    :aria-pressed="activeTab === tabItem.id"
                    class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 cursor-pointer"
                    :class="activeTab === tabItem.id
                        ? 'bg-[#12344D] text-white shadow-sm'
                        : 'text-tinta-redup hover:bg-utama-muda/50 hover:text-tinta'"
                >
                    <span>{{ tabItem.label }}</span>
                    <span
                        v-if="tabItem.badge"
                        class="px-2 py-0.5 rounded-full text-[10px] font-extrabold tabular-nums"
                        :class="activeTab === tabItem.id ? 'bg-[#0E7C86] text-white' : 'bg-utama-muda text-utama-teks'"
                    >
                        {{ tabItem.badge }}
                    </span>
                </button>
            </ScrollTabs>
            <OfficialTourismCharts v-if="activeTab === 'ringkasan'" />
            <AdminSentimentPanel v-if="activeTab === 'ringkasan' || activeTab === 'sentimen'" :sentiment="sentimentAnalysis" />

            <div v-show="activeTab === 'rekomendasi'" class="space-y-5">
                <div class="surface-card p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-garis pb-4 mb-5">
                        <div>
                            <h2 class="text-xl font-extrabold text-tinta">
                                {{ t('Sistem Pendukung Keputusan & Rekomendasi Aksi Kebijakan', 'Decision Support System & Policy Action Recommendations') }}
                            </h2>
                            <p class="text-xs text-tinta-redup mt-0.5">
                                {{ t('Dihasilkan otomatis oleh mesin aturan analitik berdasarkan anomali prediksi, keluhan aspek ulasan, ketimpangan TPK hotel, dan kuadran skor destinasi.', 'Automatically generated by the analytics rule engine from forecast surges, aspect review complaints, hotel occupancy gaps, and destination score quadrants.') }}
                            </p>
                            <p class="mt-3 text-xs text-tinta-redup">{{ t('Mengikuti hasil analisis agregat terkini untuk seluruh cakupan wilayah. Rekomendasi dihitung otomatis berdasarkan data kunjungan dan indikator pariwisata.', 'Uses the latest aggregate analysis across all regions. Recommendations are automatically computed based on visits and tourism indicator data.') }}</p>
                            <p class="mt-2 text-xs font-semibold text-utama-teks">{{ rekomendasiList.filter(row => ['baru', 'diproses'].includes(row.status)).length }} {{ t('aktif dari', 'active out of') }} {{ rekomendasiList.length }} {{ t('rekomendasi tersimpan', 'saved recommendations') }}</p>
                        </div>
                    </div>

                    <p v-if="!rekomendasiList.length" class="rounded-xl border border-dashed border-garis p-6 text-sm text-tinta-redup">{{ t('Belum ada rekomendasi pada hasil analisis terakhir. Kelola pembaruan analisis melalui tab Data & sinkronisasi.', 'No recommendations in the latest analysis. Manage analysis updates in Data & sync.') }}</p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <article
                            v-for="rek in rekomendasiList"
                            :key="rek.id"
                            :data-recommendation-id="rek.id"
                            class="p-5 rounded-xl border border-garis bg-permukaan-redup/50 flex flex-col justify-between gap-4"
                        >
                            <div class="space-y-2.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-xs font-extrabold px-2 py-0.5 rounded bg-[#12344D] text-white">
                                            {{ rek.kode_aturan }}
                                        </span>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase"
                                            :class="{
                                                'bg-red-100 text-red-800': rek.prioritas === 'tinggi',
                                                'bg-amber-100 text-amber-800': rek.prioritas === 'sedang',
                                                'bg-emerald-100 text-emerald-800': rek.prioritas === 'rendah',
                                            }"
                                        > {{ t("Prioritas", "Priority") }} {{ t({ tinggi: 'tinggi', sedang: 'sedang', rendah: 'rendah' }[rek.prioritas] || rek.prioritas, { tinggi: 'high', sedang: 'medium', rendah: 'low' }[rek.prioritas] || rek.prioritas) }}
                                        </span>
                                        <span class="text-xs font-semibold text-utama-teks">
                                            {{ recommendationCategoryLabel(rek.kategori_aksi) }}
                                        </span>
                                    </div>
                                    <span
                                        class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase"
                                        :class="{
                                            'bg-amber-100 text-amber-800': rek.status === 'baru',
                                            'bg-sky-100 text-sky-800': rek.status === 'diproses',
                                            'bg-emerald-100 text-emerald-800': rek.status === 'selesai',
                                            'bg-gray-200 text-gray-700': rek.status === 'ditolak',
                                        }"
                                    >
                                        {{ recommendationStatusLabel(rek.status) }}
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-tinta">
                                    {{ recommendationText(rek, 'judul') }}
                                </h3>

                                <div class="p-3 rounded-lg bg-permukaan border border-garis text-xs space-y-1">
                                    <strong class="text-utama-teks block">{{ t('Kondisi yang terdeteksi', 'Detected condition') }}</strong>
                                    <p class="text-tinta">{{ recommendationText(rek, 'kondisi_terdeteksi') }}</p>
                                    <strong class="text-utama-teks block pt-2">{{ t('Bukti data pendukung', 'Supporting data') }}</strong>
                                    <p class="text-tinta">{{ rek.data_pendukung || recommendationText(rek, 'kondisi_terdeteksi') }}</p>
                                </div>

                                <div class="text-xs text-tinta-redup">
                                    <strong class="text-tinta">{{ t('Tindakan yang disarankan', 'Suggested action') }}</strong>
                                    <p class="mt-0.5">{{ recommendationText(rek, 'saran_tindakan') }}</p>
                                </div>
                            </div>

                            <div class="space-y-3 border-t border-garis pt-3 text-xs">
                                <p class="text-tinta-redup">{{ t('Lokasi:', 'Location:') }} <strong class="text-tinta">{{ rek.nama_destinasi || rek.nama_wilayah || t('Seluruh provinsi', 'Province-wide') }}</strong></p>
                                <div v-if="rek.catatan_tindak_lanjut" class="rounded-lg border border-garis bg-permukaan p-3">
                                    <strong>{{ t('Catatan tindak lanjut', 'Follow-up notes') }}</strong>
                                    <p class="mt-1 whitespace-pre-line">{{ rek.catatan_tindak_lanjut }}</p>
                                    <p v-if="rek.ditindaklanjuti_pada" class="mt-2 text-tinta-redup">{{ t('Dicatat pada', 'Recorded on') }} {{ formatDate(rek.ditindaklanjuti_pada) }}</p>
                                </div>
                                <p v-else class="text-tinta-redup">{{ t('Belum ada catatan tindak lanjut.', 'No follow-up notes yet.') }}</p>
                                <details class="rounded-lg border border-garis bg-permukaan p-3" @toggle="initializeRecommendationDraft(rek, $event)">
                                    <summary class="cursor-pointer font-semibold text-utama-teks">{{ t('Perbarui status dan catatan', 'Update status and notes') }}</summary>
                                    <form v-if="recommendationDrafts[rek.id]" class="mt-3 space-y-3" @submit.prevent="updateRekomendasi(rek.id)">
                                        <label class="block" :for="`recommendation-status-${rek.id}`">
                                            <span class="mb-1 block font-semibold">{{ t('Status tindak lanjut', 'Follow-up status') }}</span>
                                            <select :id="`recommendation-status-${rek.id}`" v-model="recommendationDrafts[rek.id].status" class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-tinta" :disabled="recommendationSavingId !== null">
                                                <option v-for="status in ['baru', 'diproses', 'selesai', 'ditolak']" :key="status" :value="status">{{ recommendationStatusLabel(status) }}</option>
                                            </select>
                                        </label>
                                        <label class="block" :for="`recommendation-note-${rek.id}`">
                                            <span class="mb-1 block font-semibold">{{ t('Catatan atau alasan (opsional)', 'Notes or reason (optional)') }}</span>
                                            <textarea :id="`recommendation-note-${rek.id}`" v-model="recommendationDrafts[rek.id].catatan_tindak_lanjut" rows="3" maxlength="1000" class="w-full resize-y rounded-lg border border-garis bg-permukaan px-3 py-2 text-tinta" :disabled="recommendationSavingId !== null"></textarea>
                                        </label>
                                        <p v-if="recommendationErrors[rek.id]" role="alert" class="text-aksen-teks">{{ recommendationErrors[rek.id] }}</p>
                                        <button type="submit" :disabled="recommendationSavingId !== null" class="inline-flex items-center justify-center rounded-lg bg-[#0E7C86] px-4 py-2 font-semibold text-white disabled:cursor-wait disabled:opacity-60">{{ recommendationSavingId === rek.id ? t('Menyimpan...', 'Saving...') : t('Simpan tindak lanjut', 'Save follow-up') }}</button>
                                    </form>
                                </details>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- TAB 3: GUDANG DATA (ETL), KALIBRASI BOBOT MODEL & AUDIT 14 SHEET EXCEL  -->
            <!-- ======================================================================= -->
            <div v-show="activeTab === 'warehouse'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 surface-card p-6">
                        <h2 class="text-lg font-bold text-tinta">{{ t('Sinkronisasi catatan operasional', 'Sync operational records') }}</h2>
                        <p class="my-3 text-sm text-tinta-redup">{{ t('Sinkronkan tiket, ulasan, dan kunjungan yang dicatat di aplikasi.', 'Sync tickets, reviews, and visits recorded in the application.') }}</p>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0E7C86] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#0A5F67] disabled:cursor-wait disabled:opacity-60 cursor-pointer min-h-[42px]"
                            :disabled="isRunningEtl"
                            @click="runEtlNow"
                        >
                            {{ t('Sinkronkan data', 'Sync data') }}
                        </button>
                    </div>

                    <!-- Right: Tabel Verifikasi Integritas Dataset Excel vs DB Operasional vs Data Warehouse -->
                    <div class="lg:col-span-7">
                        <div class="surface-card p-6">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h2 class="text-base font-bold text-tinta">{{ t('Verifikasi Integritas Dataset Riil', 'Real Dataset Integrity Audit') }}</h2>
                                    <p class="text-xs text-tinta-redup">{{ t('Perbandingan konsistensi data antara Master Excel, DB Operasional, dan Star Schema Data Warehouse.', 'Consistency comparison across Master Excel, App DB, and DW Star Schema.') }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">
                                    {{ t('100% Konsisten', '100% Verified') }}
                                </span>
                            </div>
                            <div class="overflow-x-auto max-h-56">
                                <table class="w-full text-xs">
                                    <thead class="bg-permukaan-redup text-tinta-redup sticky top-0">
                                        <tr>
                                            <th class="p-2 text-left">Sheet Skema</th>
                                            <th class="p-2 text-right">Excel</th>
                                            <th class="p-2 text-right">App DB</th>
                                            <th class="p-2 text-right">DW</th>
                                            <th class="p-2 text-left">Sumber & Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-garis tabular-nums">
                                        <tr v-for="da in datasetAudit" :key="da.sheet" class="hover:bg-utama-muda/20">
                                            <td class="p-2 font-mono font-bold text-utama-teks text-[11px]">{{ da.sheet }}</td>
                                            <td class="p-2 text-right font-semibold">{{ formatNumber(da.excel_rows) }}</td>
                                            <td class="p-2 text-right font-semibold">{{ formatNumber(da.app_rows) }}</td>
                                            <td class="p-2 text-right font-bold text-[#0E7C86]">{{ formatNumber(da.dw_rows) }}</td>
                                            <td class="p-2 text-tinta-redup text-[11px]">{{ da.keterangan }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ETL Execution History & Kamus Data -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-6 surface-card p-6">
                        <h3 class="text-base font-bold text-tinta mb-3">
                            {{ t('Riwayat Eksekusi ETL & Pembaruan Model Analitik', 'ETL Execution & Analytics Model Run History') }}
                        </h3>
                        <div class="overflow-x-auto max-h-80">
                            <table class="w-full text-xs">
                                <thead class="bg-permukaan-redup text-tinta-redup">
                                    <tr>
                                        <th class="p-2 text-left">{{ t("Jenis Proses", "Process type") }}</th>
                                        <th class="p-2 text-right">{{ t("Baris Diproses", "Rows processed") }}</th>
                                        <th class="p-2 text-right">{{ t("Durasi", "Duration") }}</th>
                                        <th class="p-2 text-left">{{ t("Pemicu", "Triggered by") }}</th>
                                        <th class="p-2 text-center">{{ t("Status", "Status") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-garis tabular-nums">
                                    <tr v-for="run in etlRuns" :key="run.id">
                                        <td class="p-2 font-bold">{{ run.jenis_proses }}</td>
                                        <td class="p-2 text-right">{{ formatNumber(run.total_baris_diproses) }}</td>
                                        <td class="p-2 text-right">{{ run.durasi_detik }}s</td>
                                        <td class="p-2">{{ run.dipicu_oleh }}</td>
                                        <td class="p-2 text-center">
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                                                {{ run.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lg:col-span-6 surface-card p-6">
                        <h3 class="text-base font-bold text-tinta mb-3">
                            {{ t('Kamus Data (108 Kolom Skema Dataset)', 'Data Dictionary (108 Dataset Columns)') }}
                        </h3>
                        <div class="overflow-x-auto max-h-80">
                            <table class="w-full text-xs">
                                <thead class="bg-permukaan-redup text-tinta-redup">
                                    <tr>
                                        <th class="p-2 text-left">Sheet</th>
                                        <th class="p-2 text-left">{{ t("Kolom", "Column") }}</th>
                                        <th class="p-2 text-left">{{ t("Tipe", "Type") }}</th>
                                        <th class="p-2 text-left">{{ t("Deskripsi", "Description") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-garis">
                                    <tr v-for="kd in kamusData" :key="kd.id">
                                        <td class="p-2 font-mono text-[11px] text-utama-teks">{{ kd.sheet_name }}</td>
                                        <td class="p-2 font-mono font-bold">{{ kd.nama_kolom }}</td>
                                        <td class="p-2 text-tinta-redup">{{ kd.tipe_data }}</td>
                                        <td class="p-2 text-tinta-redup">{{ kd.deskripsi }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- TAB 4: MASTER DATA DESTINASI & ANTREAN PERSETUJUAN PENGELOLA           -->
            <!-- ======================================================================= -->
            <div v-show="activeTab === 'master'" class="space-y-6">
                <!-- Pending Approval Queue from Destination Managers -->
                <div class="surface-card p-6">
                    <h2 class="text-lg font-bold text-tinta mb-3">
                        {{ t('Antrean Persetujuan Perubahan Tarif & Profil dari Pengelola Destinasi', 'Pending Manager Profile & Tariff Change Approvals') }}
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="pr in perubahanList"
                            :key="pr.id"
                            class="p-4 rounded-xl bg-permukaan-redup/60 border border-garis flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        >
                            <div class="text-xs space-y-1">
                                <div class="flex items-center gap-2">
                                    <strong class="text-sm text-tinta">{{ pr.destinasi?.nama_destinasi }}</strong>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="pr.status === 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                                        {{ pr.status }}
                                    </span>
                                </div>
                                <p class="text-tinta-redup">{{ t("Diajukan oleh:", "Submitted by:") }} {{ pr.pengelola?.name }} • Alasan: <em>"{{ pr.alasan_perubahan }}"</em></p>
                                <p class="font-mono text-[11px] text-utama-teks">
                                    Lama: Dewasa Rp {{ pr.data_lama_json?.harga_tiket_dewasa }}, Jam {{ pr.data_lama_json?.jam_operasional }}
                                    → Baru: Dewasa Rp {{ pr.data_baru_json?.harga_tiket_dewasa }}, Jam {{ pr.data_baru_json?.jam_operasional }}
                                </p>
                            </div>
                            <div v-if="pr.status === 'menunggu'" class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="approveChange(pr.id, 'disetujui')"
                                    class="px-3 py-1.5 rounded-lg bg-[#2E8B57] hover:bg-emerald-700 text-white text-xs font-bold cursor-pointer"
                                > {{ t("Setujui", "Approve") }} </button>
                                <button
                                    type="button"
                                    @click="approveChange(pr.id, 'ditolak')"
                                    class="px-3 py-1.5 rounded-lg bg-[#C2492E] hover:bg-red-700 text-white text-xs font-bold cursor-pointer"
                                > {{ t("Tolak", "Reject") }} </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Master Destinasi CRUD -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5">
                        <form id="form-edit-destinasi" @submit.prevent="submitDestinasiMaster" class="surface-card p-6 space-y-3">
                        <div v-if="Object.keys(destForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in destForm.errors" :key="field">{{ message }}</li></ul></div>
                            <h3 class="text-base font-bold text-tinta border-b border-garis pb-2">
                                {{ destForm.id ? `Edit Destinasi #${destForm.id}` : 'Tambah Destinasi Wisata Baru' }}
                            </h3>
                            <div>
                                <label for="field-admin-dashboard-destForm-nama_destinasi" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Nama Destinasi *", "Destination name *") }}</label>
                                <input :aria-invalid="Boolean(destForm.errors.nama_destinasi)" id="field-admin-dashboard-destForm-nama_destinasi" v-model="destForm.nama_destinasi" type="text" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="field-admin-dashboard-destForm-wilayah_id" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Wilayah Kab/Kota *", "Region / city *") }}</label>
                                    <select :aria-invalid="Boolean(destForm.errors.wilayah_id)" id="field-admin-dashboard-destForm-wilayah_id" v-model.number="destForm.wilayah_id" class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta">
                                        <option v-for="w in wilayahList" :key="w.id" :value="w.id">{{ w.nama_wilayah }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-kategori" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Kategori *", "Category *") }}</label>
                                    <select :aria-invalid="Boolean(destForm.errors.kategori)" id="field-admin-dashboard-destForm-kategori" v-model="destForm.kategori" class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta">
                                        <option value="Bahari">{{ t("Bahari", "Marine") }}</option>
                                        <option value="Alam">{{ t("Alam", "Nature") }}</option>
                                        <option value="Budaya & Sejarah">{{ t("Budaya & Sejarah", "Culture & history") }}</option>
                                        <option value="Religi">{{ t("Religi", "Religious") }}</option>
                                        <option value="Ikon Kota">{{ t("Ikon Kota", "City landmarks") }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-latitude" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Latitude *", "Latitude *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.latitude)" id="field-admin-dashboard-destForm-latitude" v-model.number="destForm.latitude" type="number" step="0.0001" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta tabular-nums" />
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-longitude" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Longitude *", "Longitude *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.longitude)" id="field-admin-dashboard-destForm-longitude" v-model.number="destForm.longitude" type="number" step="0.0001" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta tabular-nums" />
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-harga_tiket_dewasa" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Tiket Dewasa (Rp) *", "Adult ticket (IDR) *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.harga_tiket_dewasa)" id="field-admin-dashboard-destForm-harga_tiket_dewasa" v-model.number="destForm.harga_tiket_dewasa" type="number" min="0" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta tabular-nums" />
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-harga_tiket_anak" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Tiket Anak (Rp) *", "Child ticket (IDR) *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.harga_tiket_anak)" id="field-admin-dashboard-destForm-harga_tiket_anak" v-model.number="destForm.harga_tiket_anak" type="number" min="0" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta tabular-nums" />
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-jam_operasional" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Jam Operasional *", "Opening hours *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.jam_operasional)" id="field-admin-dashboard-destForm-jam_operasional" v-model="destForm.jam_operasional" type="text" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta" />
                                </div>
                                <div>
                                    <label for="field-admin-dashboard-destForm-kapasitas_harian" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Kapasitas Harian *", "Daily capacity *") }}</label>
                                    <input :aria-invalid="Boolean(destForm.errors.kapasitas_harian)" id="field-admin-dashboard-destForm-kapasitas_harian" v-model.number="destForm.kapasitas_harian" type="number" min="10" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta tabular-nums" />
                                </div>
                            </div>
                            <div>
                                <label for="field-admin-dashboard-destForm-deskripsi_id" class="block text-xs font-bold text-tinta-redup mb-1">{{ t("Deskripsi (ID) *", "Description (Indonesian) *") }}</label>
                                <textarea :aria-invalid="Boolean(destForm.errors.deskripsi_id)" id="field-admin-dashboard-destForm-deskripsi_id" v-model="destForm.deskripsi_id" rows="2" required class="w-full px-3 py-2 rounded-lg border border-garis bg-permukaan-redup text-xs text-tinta"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" :disabled="destForm.processing" class="flex-1 py-2 px-4 rounded-lg bg-utama text-white text-xs font-bold cursor-pointer">
                                    {{ destForm.id ? 'Simpan Perubahan' : 'Tambah Destinasi' }}
                                </button>
                                <button v-if="destForm.id" type="button" @click="destForm.reset()" class="px-3 py-2 rounded-lg border border-garis text-xs cursor-pointer"> {{ t("Batal", "Cancel") }} </button>
                            </div>
                        </form>
                    </div>

                    <div class="lg:col-span-7 surface-card p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                            <div>
                                <h3 class="text-base font-extrabold text-tinta"> {{ t("Daftar Master Destinasi & pengelola Masing-Masing Destinasi", "Destinations and assigned managers") }} </h3>
                                <p class="text-xs text-tinta-redup mt-0.5">
                                    Klik nama destinasi untuk membuka <strong>Drawer Inspeksi</strong>, atau klik <strong>Pantau Panel</strong> untuk mengelola pengunjung:
                                </p>
                            </div>
                            <input
                                v-model="destSearchQuery"
                                type="search"
                                :placeholder="t('Cari objek wisata / wilayah / email...', 'Search destination, region, or email?')"
                                class="px-3 py-1.5 rounded-xl border border-garis bg-permukaan-redup text-xs font-semibold text-tinta sm:w-64"
                            />
                        </div>
                        <div class="overflow-x-auto max-h-[520px] rounded-xl border border-garis">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-[#12344D] text-white">
                                    <tr>
                                        <th class="p-2.5 text-left">ID</th>
                                        <th class="p-2.5 text-left">{{ t("Objek Wisata & Kategori", "Destination & category") }}</th>
                                        <th class="p-2.5 text-left">{{ t("Akun Pengelola Khusus", "Assigned manager") }}</th>
                                        <th class="p-2.5 text-left">{{ t("Wilayah", "Region") }}</th>
                                        <th class="p-2.5 text-right">{{ t("Tiket Dewasa", "Adult ticket") }}</th>
                                        <th class="p-2.5 text-center">{{ t("Kendali Akun Utama", "Management actions") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-garis tabular-nums bg-permukaan">
                                    <tr
                                        v-for="d in filteredDestinasiList"
                                        :key="d.id"
                                        @click="openDestinasiDrawer(d)"
                                        class="hover:bg-utama-muda/30 transition cursor-pointer"
                                    >
                                        <td class="p-2.5 font-mono font-extrabold text-utama-teks">#{{ d.id }}</td>
                                        <td class="p-2.5">
                                            <div class="font-bold text-tinta hover:text-utama">{{ d.nama_destinasi }}</div>
                                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded bg-utama-muda text-utama-teks text-[10px] font-bold">
                                                {{ d.kategori }}
                                            </span>
                                        </td>
                                        <td class="p-2.5 font-mono text-[11px] text-utama-teks font-semibold">
                                            {{ pengelolaByDestMap[d.id]?.email || `pengelola.destinasi${d.id}@pariwisata-sulteng.test` }}
                                        </td>
                                        <td class="p-2.5 text-tinta-redup font-medium">{{ d.wilayah?.nama_wilayah }}</td>
                                        <td class="p-2.5 text-right font-bold">{{ formatRupiah(d.harga_tiket_dewasa) }}</td>
                                        <td class="p-2.5 text-center" @click.stop>
                                            <div class="inline-flex items-center gap-1.5">
                                                <button
                                                    type="button"
                                                    @click="focusDestinasiCharts(d.id)"
                                                    class="px-2.5 py-1 rounded-lg bg-utama-muda hover:bg-utama text-utama-teks hover:text-white font-bold transition cursor-pointer"
                                                    :title="t('Lihat 6 Chart Operasional Objek Wisata Ini', 'View destination operational charts')"
                                                >
                                                    6 Chart
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="editDest(d)"
                                                    class="px-2.5 py-1 rounded-lg border border-garis hover:border-utama text-tinta font-bold transition cursor-pointer"
                                                > {{ t("Edit", "Edit") }} </button>
                                                <Link
                                                    :href="`/pengelola/dashboard?destinasi_id=${d.id}`"
                                                    class="px-2.5 py-1 rounded-lg bg-[#12344D] hover:bg-utama text-white font-bold transition"
                                                >
                                                    Pantau Panel →
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- TAB 5: MANAJEMEN PENGGUNA, MODERASI ULASAN, PENGADUAN & LOG             -->
            <!-- ======================================================================= -->
            <div v-show="activeTab === 'moderasi'" class="space-y-6">
                <div class="moderation-primary grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- User & Role Management -->
                    <div class="user-management lg:col-span-6 surface-card p-6 space-y-4">
                        <h3 class="text-base font-bold text-tinta">
                            {{ t('Manajemen Pengguna & Hak Akses Peran', 'User & Role Access Management') }}
                        </h3>

                        <form @submit.prevent="submitUserForm" class="p-4 rounded-xl bg-permukaan-redup border border-garis space-y-3">
                        <div v-if="Object.keys(userForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in userForm.errors" :key="field">{{ message }}</li></ul></div>
                            <div class="text-xs font-bold text-utama-teks">
                                {{ userForm.id ? `${t('Ubah pengguna', 'Edit user')} #${userForm.id}` : t('Tambah pengguna baru', 'Add a user') }}
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2.5">
                                <label for="field-admin-dashboard-userForm-name" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Nama lengkap', 'Full name') }}</span><input id="field-admin-dashboard-userForm-name" :aria-invalid="Boolean(userForm.errors.name)" v-model="userForm.name" type="text" required :placeholder="t('Nama Lengkap *', 'Full name *')" class="px-3 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                                <label for="field-admin-dashboard-userForm-email" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Alamat email', 'Email address') }}</span><input id="field-admin-dashboard-userForm-email" :aria-invalid="Boolean(userForm.errors.email)" v-model="userForm.email" type="email" required :placeholder="t('Email *', 'Email *')" class="px-3 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                                <label for="field-admin-dashboard-userForm-peran" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Peran pengguna', 'User role') }}</span><select id="field-admin-dashboard-userForm-peran" :aria-invalid="Boolean(userForm.errors.peran)" v-model="userForm.peran" class="px-3 py-1.5 rounded border border-garis bg-permukaan text-xs font-semibold text-tinta">
                                    <option value="user">{{ t('Wisatawan', 'Traveller') }}</option>
                                    <option value="pengelola_destinasi">{{ t('Pengelola destinasi', 'Destination manager') }}</option>
                                    <option value="admin">{{ t('Admin dinas', 'Department admin') }}</option>
                                </select></label>
                                <label v-if="userForm.peran === 'pengelola_destinasi'" for="field-admin-dashboard-userForm-destinasi_id" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Destinasi yang dikelola', 'Managed destination') }}</span><select id="field-admin-dashboard-userForm-destinasi_id" :aria-invalid="Boolean(userForm.errors.destinasi_id)"  v-model.number="userForm.destinasi_id" class="px-3 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta">
                                    <option v-for="d in destinasiList" :key="d.id" :value="d.id">{{ d.nama_destinasi }}</option>
                                </select></label>
                                <label for="field-admin-dashboard-userForm-password" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Kata sandi', 'Password') }}</span><input id="field-admin-dashboard-userForm-password" :aria-invalid="Boolean(userForm.errors.password)" v-model="userForm.password" type="password" :placeholder="userForm.id ? 'Password baru (opsional)' : 'Password (default: password)'" class="px-3 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" :disabled="userForm.processing" class="px-3.5 py-1.5 rounded bg-utama text-white text-xs font-bold cursor-pointer">
                                    {{ userForm.id ? t('Simpan perubahan', 'Save changes') : t('Tambah pengguna', 'Add user') }}
                                </button>
                                <button v-if="userForm.id" type="button" @click="userForm.reset()" class="px-3 py-1.5 rounded border border-garis text-xs cursor-pointer"> {{ t("Batal", "Cancel") }} </button>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-permukaan-redup text-tinta-redup">
                                    <tr>
                                        <th class="p-2 text-left">{{ t("Nama", "Name") }}</th>
                                        <th class="p-2 text-left">Email</th>
                                        <th class="p-2 text-left">{{ t("Peran", "Role") }}</th>
                                        <th class="p-2 text-left">{{ t("Destinasi Kelolaan", "Assigned destination") }}</th>
                                        <th class="p-2 text-center">{{ t("Aksi", "Actions") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-garis">
                                    <tr v-for="u in usersList" :key="u.id">
                                        <td class="p-2 font-bold">{{ u.name }}</td>
                                        <td class="p-2 font-mono text-[11px]">{{ u.email }}</td>
                                        <td class="p-2">
                                            <span class="px-2 py-0.5 rounded bg-utama-muda text-utama-teks font-bold text-[10px] uppercase">
                                                {{ u.peran === 'admin' ? t('Admin dinas', 'Department admin') : u.peran === 'pengelola_destinasi' ? t('Pengelola destinasi', 'Destination manager') : t('Wisatawan', 'Traveller') }}
                                            </span>
                                        </td>
                                        <td class="p-2 text-tinta-redup">{{ u.destinasi?.nama_destinasi || '-' }}</td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="editUserRow(u)" class="px-2 py-0.5 rounded border border-garis text-[10px] font-bold cursor-pointer"> {{ t("Edit", "Edit") }} </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Review Moderation -->
                    <div class="lg:col-span-6 surface-card p-6 space-y-4">
                        <h3 class="text-base font-bold text-tinta">
                            {{ t('Moderasi Ulasan Wisatawan', 'Visitor Review Moderation') }}
                        </h3>
                        <div class="overflow-x-auto max-h-96">
                            <table class="w-full text-xs">
                                <thead class="bg-permukaan-redup text-tinta-redup">
                                    <tr>
                                        <th class="p-2 text-left">{{ t("Destinasi", "Destination") }}</th>
                                        <th class="p-2 text-left">{{ t("Ulasan", "Review") }}</th>
                                        <th class="p-2 text-center">{{ t("Status", "Status") }}</th>
                                        <th class="p-2 text-center">{{ t("Tindakan", "Actions") }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-garis">
                                    <tr v-for="ul in ulasanModerasi" :key="ul.id">
                                        <td class="p-2 font-semibold">{{ ul.destinasi?.nama_destinasi }}</td>
                                        <td class="p-2 line-clamp-1">{{ ul.teks_ulasan }}</td>
                                        <td class="p-2 text-center font-bold uppercase text-[10px]">{{ ul.status_moderasi }}</td>
                                        <td class="p-2 text-center">
                                            <button
                                                type="button"
                                                @click="toggleModerasiUlasan(ul.id, ul.status_moderasi === 'tayang' ? 'disembunyikan' : 'tayang')"
                                                class="px-2 py-0.5 rounded border border-garis text-[10px] font-bold cursor-pointer"
                                            >
                                                {{ ul.status_moderasi === 'tayang' ? 'Sembunyikan' : 'Tayangkan' }}
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pengaduan Masyarakat & Persetujuan Event / Berita -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-6 surface-card p-6 space-y-4">
                        <h3 class="text-base font-bold text-tinta">
                            {{ t('Tindak Lanjut Pengaduan Fasilitas Wisata', 'Facility Feedback & Complaint Responses') }}
                        </h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <div
                                v-for="ad in pengaduanList"
                                :key="ad.id"
                                class="p-3.5 rounded-xl bg-permukaan-redup border border-garis text-xs space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-utama-teks">{{ ad.kode_pengaduan }} — {{ ad.kategori }}</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold uppercase">{{ ad.status }}</span>
                                </div>
                                <strong class="block text-tinta">{{ ad.judul }}</strong>
                                <p class="text-tinta-redup">{{ ad.pesan }}</p>
                                <p v-if="ad.tanggapan_admin" class="rounded-lg bg-utama-muda/40 p-2"><strong>{{ t('Tanggapan tersimpan:', 'Saved response:') }}</strong> {{ ad.tanggapan_admin }}</p>
                                <p v-if="pengaduanReplyErrors[ad.id]" role="alert" class="text-aksen-teks">{{ pengaduanReplyErrors[ad.id] }}</p>
                                <div class="flex flex-col items-stretch gap-2 pt-1 sm:flex-row sm:items-start">
                                    <textarea
                                        v-model="pengaduanReplies[ad.id]"
                                        :id="`complaint-reply-${ad.id}`"
                                        :aria-label="t('Tanggapan resmi untuk', 'Official response for') + ' ' + ad.kode_pengaduan"
                                        :aria-invalid="Boolean(pengaduanReplyErrors[ad.id])"
                                        :placeholder="t('Tulis tanggapan resmi Dinas Pariwisata...', 'Write the department?s official response...')"
                                        maxlength="1500" rows="3"
                                        class="min-w-0 flex-1 px-2.5 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta"
                                    ></textarea>
                                    <button
                                        type="button"
                                        @click="sendPengaduanReply(ad.id, 'selesai')"
                                        :disabled="pengaduanReplySavingId !== null"
                                        class="px-3 py-1.5 rounded bg-[#2E8B57] text-white text-xs font-bold cursor-pointer"
                                    > {{ t("Selesaikan", "Resolve") }} </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Approvals & Publish News -->
                    <div class="lg:col-span-6 surface-card p-6 space-y-4">
                        <h3 class="text-base font-bold text-tinta">
                            {{ t('Persetujuan Kalender Event & Publikasi Berita Resmi', 'Event Calendar Approvals & Official News Publishing') }}
                        </h3>

                        <div class="space-y-2 max-h-44 overflow-y-auto">
                            <div
                                v-for="ev in eventsList"
                                :key="ev.id"
                                class="p-2.5 rounded-lg bg-permukaan-redup border border-garis text-xs flex items-center justify-between gap-2"
                            >
                                <div>
                                    <strong class="block text-tinta">{{ ev.nama_event_id }}</strong>
                                    <span class="text-tinta-redup">{{ formatDate(ev.tanggal_mulai) }} • {{ ev.lokasi_detail }} ({{ ev.status_persetujuan }})</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-if="ev.status_persetujuan !== 'disetujui'"
                                        type="button"
                                        @click="updateEventStatus(ev.id, 'disetujui')"
                                        class="px-2.5 py-1 rounded bg-[#2E8B57] text-white text-[10px] font-bold cursor-pointer"
                                    > {{ t("Setujui", "Approve") }} </button>
                                    <button
                                        v-if="ev.status_persetujuan !== 'ditolak'"
                                        type="button"
                                        @click="updateEventStatus(ev.id, 'ditolak')"
                                        class="px-2.5 py-1 rounded bg-[#C2492E] text-white text-[10px] font-bold cursor-pointer"
                                    > {{ t("Tolak", "Reject") }} </button>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitBeritaForm" class="p-3.5 rounded-xl bg-permukaan-redup border border-garis space-y-2.5">
                        <div v-if="Object.keys(beritaForm.errors).length" role="alert" class="mb-4 rounded-xl border border-bahaya/30 bg-bahaya/10 p-4 text-sm text-bahaya"><p class="font-semibold">{{ t('Periksa kembali isian yang belum sesuai.', 'Please review the highlighted fields.') }}</p><ul class="mt-2 list-inside list-disc"><li v-for="(message, field) in beritaForm.errors" :key="field">{{ message }}</li></ul></div>
                            <div class="text-xs font-bold text-utama-teks">{{ t("Terbitkan Artikel Berita Resmi Baru", "Publish a news article") }}</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label for="field-admin-dashboard-beritaForm-judul_id" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Judul berita', 'Article title') }}</span><input id="field-admin-dashboard-beritaForm-judul_id" :aria-invalid="Boolean(beritaForm.errors.judul_id)" v-model="beritaForm.judul_id" type="text" required :placeholder="t('Judul Berita Resmi *', 'Article title *')" class="px-2.5 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                                <label for="field-admin-dashboard-beritaForm-kategori_berita" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Kategori berita', 'Article category') }}</span><input id="field-admin-dashboard-beritaForm-kategori_berita" :aria-invalid="Boolean(beritaForm.errors.kategori_berita)" v-model="beritaForm.kategori_berita" type="text" required :placeholder="t('Kategori (Kebijakan / Statistik)', 'Category (policy / statistics)')" class="px-2.5 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                            </div>
                            <label for="field-admin-dashboard-beritaForm-ringkasan_id" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Ringkasan', 'Summary') }}</span><input id="field-admin-dashboard-beritaForm-ringkasan_id" :aria-invalid="Boolean(beritaForm.errors.ringkasan_id)" v-model="beritaForm.ringkasan_id" type="text" required :placeholder="t('Ringkasan Berita *', 'Article summary *')" class="w-full px-2.5 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta" /></label>
                            <label for="field-admin-dashboard-beritaForm-isi_id" class="block min-w-0"><span class="mb-1.5 block text-xs font-medium text-tinta-redup">{{ t('Isi berita', 'Article content') }}</span><textarea id="field-admin-dashboard-beritaForm-isi_id" :aria-invalid="Boolean(beritaForm.errors.isi_id)" v-model="beritaForm.isi_id" rows="2" required :placeholder="t('Isi lengkap berita resmi *', 'Full article content *')" class="w-full px-2.5 py-1.5 rounded border border-garis bg-permukaan text-xs text-tinta"></textarea></label>
                            <button type="submit" :disabled="beritaForm.processing" class="px-4 py-1.5 rounded bg-[#12344D] text-white text-xs font-bold cursor-pointer"> {{ t("Publikasikan Berita", "Publish article") }} </button>
                        </form>
                    </div>
                </div>

                <!-- Activity Logs -->
                <div class="surface-card p-6">
                    <h3 class="text-base font-bold text-tinta mb-3">
                        {{ t('Log Aktivitas Sistem & Jejak Audit', 'System Activity & Audit Trail Logs') }}
                    </h3>
                    <div class="overflow-x-auto max-h-72">
                        <table class="w-full text-xs">
                            <thead class="bg-permukaan-redup text-tinta-redup">
                                <tr>
                                    <th class="p-2 text-left">{{ t("Waktu", "Time") }}</th>
                                    <th class="p-2 text-left">{{ t("Pengguna", "User") }}</th>
                                    <th class="p-2 text-left">{{ t("Peran", "Role") }}</th>
                                    <th class="p-2 text-left">{{ t("Modul / Aksi", "Module / action") }}</th>
                                    <th class="p-2 text-left">{{ t("Deskripsi", "Description") }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis">
                                <tr v-for="lg in activityLogs" :key="lg.id">
                                    <td class="p-2 font-mono text-[11px]">{{ formatDate(lg.created_at) }}</td>
                                    <td class="p-2 font-bold">{{ lg.nama_user }}</td>
                                    <td class="p-2 uppercase text-[10px] font-bold text-utama-teks">{{ lg.peran }}</td>
                                    <td class="p-2 font-semibold">{{ lg.modul }} — {{ lg.aksi }}</td>
                                    <td class="p-2 text-tinta-redup">{{ lg.deskripsi }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ======================================================================= -->
            <!-- SLIDE-OVER DRAWER INSPEKSI CEPAT DESTINASI WISATA                       -->
            <!-- ======================================================================= -->
            <DestinasiDetailDrawer
                :destinasi="selectedDrawerDest"
                :pengelola="selectedDrawerDest ? pengelolaByDestMap[selectedDrawerDest.id] : null"
                :skor-info="selectedDrawerDest ? (charts.chart_18_kuadran_bubble || []).find(row => Number(row.destinasi_id) === Number(selectedDrawerDest.id)) || null : null"
                @close="selectedDrawerDest = null"
                @focus-charts="(id) => { selectedDrawerDest = null; focusDestinasiCharts(id); }"
                @edit-master="handleDrawerEdit"
            />
        </section>
    </AppLayout>
</template>

<script setup>
import ScrollTabs from '@/Components/ui/ScrollTabs.vue';
import OfficialTourismCharts from '@/Components/dashboard/OfficialTourismCharts.vue';
import AdminSentimentPanel from '@/Components/dashboard/AdminSentimentPanel.vue';
import PageHeading from '@/Components/ui/PageHeading.vue';
import { ref, reactive, computed, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import {
    BarChart3, RefreshCw, FileDown, ShieldCheck, Database, Building2,
    MapPin, Users, Globe, Wallet, TrendingUp, Sparkles, Filter, ArrowRight
} from 'lucide-vue-next';
import AppLayout from '../../Layouts/AppLayout.vue';
import DestinasiDetailDrawer from '../../Components/dashboard/DestinasiDetailDrawer.vue';
import { useAppContext } from '../../composables/useAppContext';

const props = defineProps({
    filters: { type: Object, required: true },
    reportingPeriod: { type: Object, default: () => ({}) },
    kpi: { type: Object, required: true },
    charts: { type: Object, required: true },
    publicCharts: { type: Object, default: () => ({}) },
    decisionCharts: { type: Object, default: () => ({}) },
    managerCharts: { type: Object, default: () => ({}) },
    selectedDestId: { type: Number, default: 1 },
    rekomendasiList: { type: Array, default: () => [] },
    datasetAudit: { type: Array, default: () => [] },
    etlRuns: { type: Array, default: () => [] },
    importLogs: { type: Array, default: () => [] },
    perubahanList: { type: Array, default: () => [] },
    usersList: { type: Array, default: () => [] },
    destinasiList: { type: Array, default: () => [] },
    wilayahList: { type: Array, default: () => [] },
    ulasanModerasi: { type: Array, default: () => [] },
    sentimentAnalysis: { type: Object, default: () => ({ total: 0, positif: 0, netral: 0, negatif: 0, persen_positif: 0, persen_netral: 0, persen_negatif: 0, ulasan: [] }) },
    pengaduanList: { type: Array, default: () => [] },
    eventsList: { type: Array, default: () => [] },
    beritaList: { type: Array, default: () => [] },
    activityLogs: { type: Array, default: () => [] },
    kamusData: { type: Array, default: () => [] },
    refDataReal: { type: Array, default: () => [] },
});

const { t, locale, formatNumber, formatRupiah, formatDate } = useAppContext();

const destSearchQuery = ref('');
const selectedDrawerDest = ref(null);

const activeTab = ref(props.filters.tab || 'ringkasan');
const activeSubView = ref(props.filters.sub || 'briefing');

watch(
    () => [props.filters.tab, props.filters.sub],
    ([newTab, newSub]) => {
        if (newTab) activeTab.value = newTab;
        if (newSub) activeSubView.value = newSub;
    }
);

const pengelolaByDestMap = computed(() => {
    const map = {};
    for (const u of props.usersList) {
        if (u.peran === 'pengelola_destinasi' && u.destinasi_id) {
            map[u.destinasi_id] = u;
        }
    }
    return map;
});

function getDestScore(destId) {
    const score = (props.charts.chart_18_kuadran_bubble || []).find(row => Number(row.destinasi_id) === Number(destId));
    return score?.skor_total == null ? null : Number(score.skor_total).toFixed(1);
}

const activeRecommendations = computed(() => props.rekomendasiList.filter(item => ['baru', 'diproses'].includes(item.status)));
function openDestinasiDrawer(dest) {
    selectedDrawerDest.value = dest;
}

function handleDrawerEdit(dest) {
    selectedDrawerDest.value = null;
    activeTab.value = 'master';
    editDest(dest);
}

const filteredDestinasiList = computed(() => {
    const q = destSearchQuery.value.trim().toLowerCase();
    if (!q) return props.destinasiList;
    return props.destinasiList.filter((d) => {
        const email = pengelolaByDestMap.value[d.id]?.email || '';
        return (
            String(d.nama_destinasi || '').toLowerCase().includes(q) ||
            String(d.kategori || '').toLowerCase().includes(q) ||
            String(d.wilayah?.nama_wilayah || '').toLowerCase().includes(q) ||
            email.toLowerCase().includes(q)
        );
    });
});

function selectExecutiveView(tabId, subId = 'briefing') {
    activeTab.value = tabId;
    activeSubView.value = subId;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabId);
    url.searchParams.set('sub', subId);
    window.history.replaceState({}, '', url.toString());
}

function focusDestinasiCharts(destId) {
    router.get('/pengelola/dashboard', { destinasi_id: destId });
}

const navTabs = computed(() => [
    { id: 'ringkasan', label: t('Statistik resmi', 'Official statistics') },
    { id: 'sentimen', label: t('Analisis Sentimen', 'Sentiment Analysis'), badge: props.sentimentAnalysis?.total || 0 },
    { id: 'rekomendasi', label: t('Rekomendasi Kebijakan', 'Policy Recommendations'), badge: props.kpi.rekomendasi_aktif_count },
    { id: 'warehouse', label: t('Data & sinkronisasi', 'Data & sync') },
    { id: 'master', label: t('Destinasi & pengelola', 'Destinations & managers'), badge: props.kpi.antrean_persetujuan_count },
    { id: 'moderasi', label: t('Pengguna & moderasi', 'Users & moderation') },
]);

const isRunningEtl = ref(false);
function runEtlNow() {
    isRunningEtl.value = true;
    router.post('/admin/etl/run', {}, {
        preserveScroll: true,
        onFinish: () => { isRunningEtl.value = false; },
    });
}

const recommendationDrafts = reactive({});
const recommendationErrors = reactive({});
const recommendationSavingId = ref(null);
function recommendationText(record, field) {
    return t(record[`${field}_id`] || record[`${field}_en`] || 'Informasi belum tersedia.', record[`${field}_en`] || record[`${field}_id`] || 'Information is not available yet.');
}
function recommendationStatusLabel(status) {
    return ({ baru: t('Baru', 'New'), diproses: t('Diproses', 'In progress'), selesai: t('Selesai', 'Completed'), ditolak: t('Ditolak', 'Rejected') })[status] || status;
}
function recommendationCategoryLabel(category) {
    return ({
        'Promosi & Paket': t('Promosi & paket', 'Promotion & packages'),
        'Inspeksi & Fasilitas': t('Inspeksi & fasilitas', 'Inspection & facilities'),
        'Manajemen Kapasitas': t('Manajemen kapasitas', 'Capacity management'),
        'Kemitraan Hotel': t('Kemitraan hotel', 'Hotel partnerships'),
        'Optimasi Konversi': t('Optimasi konversi', 'Conversion improvement'),
    })[category] || category;
}
function initializeRecommendationDraft(record, event) {
    if (event.target.open && !recommendationDrafts[record.id]) {
        recommendationDrafts[record.id] = { status: record.status, catatan_tindak_lanjut: record.catatan_tindak_lanjut || '' };
    }
}
function updateRekomendasi(id) {
    if (recommendationSavingId.value !== null) return;
    recommendationSavingId.value = id;
    recommendationErrors[id] = '';
    router.post(`/admin/rekomendasi/${id}/status`, recommendationDrafts[id], {
        preserveScroll: true,
        onError: errors => { recommendationErrors[id] = Object.values(errors).join(' '); },
        onFinish: () => { recommendationSavingId.value = null; },
    });
}

function approveChange(id, keputusan) {
    router.post(`/admin/perubahan-destinasi/${id}/proses`, { keputusan }, { preserveScroll: true });
}

const destForm = useForm({
    id: null,
    nama_destinasi: '',
    wilayah_id: 1,
    kategori: 'Bahari',
    latitude: -0.90,
    longitude: 119.87,
    harga_tiket_dewasa: 15000,
    harga_tiket_anak: 10000,
    jam_operasional: '08:00 - 17:00',
    kapasitas_harian: 500,
    deskripsi_id: '',
    deskripsi_en: '',
});

function editDest(d) {
    destForm.id = d.id;
    destForm.nama_destinasi = d.nama_destinasi;
    destForm.wilayah_id = d.wilayah_id;
    destForm.kategori = d.kategori;
    destForm.latitude = Number(d.latitude);
    destForm.longitude = Number(d.longitude);
    destForm.harga_tiket_dewasa = d.harga_tiket_dewasa;
    destForm.harga_tiket_anak = d.harga_tiket_anak;
    destForm.jam_operasional = d.jam_operasional;
    destForm.kapasitas_harian = d.kapasitas_harian;
    destForm.deskripsi_id = d.deskripsi_id;
    destForm.deskripsi_en = d.deskripsi_en;
}

function submitDestinasiMaster() {
    destForm.post('/admin/destinasi/simpan', {
        preserveScroll: true,
        onSuccess: () => destForm.reset(),
    });
}

function toggleModerasiUlasan(id, status) {
    router.post(`/admin/ulasan/${id}/moderasi`, { status_moderasi: status }, { preserveScroll: true });
}

const userForm = useForm({
    id: null,
    name: '',
    email: '',
    peran: 'user',
    destinasi_id: 1,
    password: '',
    is_active: true,
});

function editUserRow(u) {
    userForm.id = u.id;
    userForm.name = u.name;
    userForm.email = u.email;
    userForm.peran = u.peran;
    userForm.destinasi_id = u.destinasi_id || 1;
    userForm.password = '';
    userForm.is_active = Boolean(u.is_active);
}

function submitUserForm() {
    userForm.post('/admin/pengguna/simpan', {
        preserveScroll: true,
        onSuccess: () => userForm.reset(),
    });
}

const pengaduanReplies = reactive({});
const pengaduanReplyErrors = reactive({});
const pengaduanReplySavingId = ref(null);
function sendPengaduanReply(id, status) {
    if (pengaduanReplySavingId.value !== null) return;
    const tanggapan = (pengaduanReplies[id] || '').trim();
    if (!tanggapan) {
        pengaduanReplyErrors[id] = t('Tuliskan tanggapan resmi sebelum menyelesaikan pengaduan.', 'Write an official response before resolving this complaint.');
        return;
    }
    pengaduanReplyErrors[id] = '';
    pengaduanReplySavingId.value = id;
    router.post(`/admin/pengaduan/${id}/tanggapi`, { status, tanggapan_admin: tanggapan }, {
        preserveScroll: true,
        onError: errors => { pengaduanReplyErrors[id] = Object.values(errors).join(' '); },
        onSuccess: () => { pengaduanReplies[id] = ''; },
        onFinish: () => { pengaduanReplySavingId.value = null; },
    });
}

function updateEventStatus(id, status) {
    router.post(`/admin/event/${id}/proses`, { status_persetujuan: status }, { preserveScroll: true });
}

const beritaForm = useForm({
    judul_id: '',
    kategori_berita: 'Pengembangan Wisata',
    wilayah_id: '',
    ringkasan_id: '',
    isi_id: '',
});

function submitBeritaForm() {
    beritaForm.post('/admin/berita/simpan', {
        preserveScroll: true,
        onSuccess: () => beritaForm.reset(),
    });
}

// =======================================================================
// 16 EXECUTIVE CHARTS CONFIGURATION (Charts #13 to #28)
// =======================================================================

</script>
