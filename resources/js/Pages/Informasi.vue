<template>
    <AppLayout>
        <Head :title="t('Agenda & Informasi Wisata', 'Events & Travel Information')" />
        <section class="mx-auto w-full max-w-[1440px] space-y-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <!-- Page Header -->
            <PageHeading :title="t('Agenda &amp; Informasi Wisata', 'Events &amp; Travel Information')" :section="t('Informasi wisata', 'Travel information')" :description="t('Kenali festival, temukan cerita daerah, dan sampaikan masukan untuk pariwisata Sulawesi Tengah.', 'Discover festivals and local stories, and share your feedback on tourism in Central Sulawesi.')" />
            <nav :aria-label="t('Bagian halaman informasi', 'Information page sections')" class="flex flex-wrap gap-2 border-b border-garis pb-5">
                <a href="#agenda" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-utama px-4 text-sm font-semibold text-white dark:text-[#0B1F2E] hover:bg-utama-gelap"><CalendarDays class="h-4 w-4" />{{ t('Agenda wisata', 'Events') }}</a>
                <a href="#berita" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-garis bg-permukaan px-4 text-sm font-semibold text-tinta hover:border-utama"><Newspaper class="h-4 w-4" />{{ t('Berita daerah', 'News') }}</a>
                <a href="#pengaduan" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-garis bg-permukaan px-4 text-sm font-semibold text-tinta hover:border-utama"><MessageSquareWarning class="h-4 w-4" />{{ t('Saran & pengaduan', 'Feedback') }}</a>
            </nav>

            <!-- Section 1: Kalender Event Wisata (FR-15) -->
            <div id="agenda" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-extrabold text-[#12344D] dark:text-[#F1F5F9]">
                            {{ t('Festival & Agenda Pilihan', 'Festivals & Selected Events') }}
                        </h2>
                        <p class="text-xs text-[#5A6B7C] dark:text-[#94A3B8]">
                            {{ t('Budaya, petualangan alam, dan cita rasa lokal dalam satu perjalanan.', 'Culture, outdoor adventures, and local flavours in one journey.') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                    <article
                        v-for="(ev, eventIndex) in events"
                        :key="ev.id"
                        class="flex flex-col overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs"
                        :class="eventIndex === 0 ? 'sm:col-span-2' : ''"
                    >
                        <div>
                            <div class="relative h-56 overflow-hidden bg-navy xl:h-64">
                                <img
                                    v-if="eventPhoto(ev)"
                                    :src="eventPhoto(ev).src"
                                    :alt="isEn ? eventPhoto(ev).altEn : eventPhoto(ev).altId"
                                    :style="{ objectPosition: eventPhoto(ev).position || 'center' }"
                                    :loading="eventIndex < 2 ? 'eager' : 'lazy'"
                                    decoding="async"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="flex h-full flex-col items-center justify-center gap-2 text-white/80"><CalendarDays class="h-8 w-8" />{{ t('Foto belum tersedia', 'Photo not yet available') }}</div>
                                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-[#0B1F2E]/65 via-transparent to-transparent"></div>
                                <span class="absolute top-4 left-4 rounded-lg bg-[#0B1F2E]/85 px-3 py-1.5 text-[11px] font-semibold text-white">
                                    {{ categoryLabel(ev.kategori_event) }}
                                </span>
                                <a v-if="eventPhoto(ev)?.source" :href="eventPhoto(ev).source" target="_blank" rel="noopener noreferrer" class="absolute right-3 bottom-3 inline-flex items-center gap-1 text-[10px] font-medium text-white underline decoration-white/50 underline-offset-2 hover:decoration-white">{{ t('Foto:', 'Photo:') }} {{ eventPhoto(ev).credit }}<ExternalLink class="h-3 w-3" /></a>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-utama-teks tabular-nums">
                                    <CalendarDays class="h-3.5 w-3.5 shrink-0" />
                                    <span>{{ formatDate(ev.tanggal_mulai) }} — {{ formatDate(ev.tanggal_selesai) }}</span>
                                </div>
                                <h3 class="font-heading mt-3 font-bold leading-snug text-tinta" :class="eventIndex === 0 ? 'text-xl sm:text-2xl' : 'text-lg'">
                                    {{ isEn ? (ev.nama_event_en || ev.nama_event_id) : ev.nama_event_id }}
                                </h3>
                                <p class="mt-3 flex items-start gap-1.5 text-xs leading-relaxed text-tinta-redup">
                                    <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-utama" /><span>{{ ev.lokasi_detail }}</span>
                                </p>
                                <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-tinta-redup">
                                    {{ isEn ? (ev.deskripsi_en || ev.deskripsi_id) : ev.deskripsi_id }}
                                </p>
                            </div>
                        </div>
                        <div class="mx-5 mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-garis py-4 text-xs">
                            <span class="font-bold text-[#12344D] dark:text-[#F1F5F9] tabular-nums">
                                {{ ev.harga_tiket_rp > 0 ? formatCurrency(ev.harga_tiket_rp) : t('Gratis untuk Umum', 'Free Entry') }}
                            </span>
                            <Link
                                v-if="ev.destinasi"
                                :href="`/destinasi/${ev.destinasi.slug || ev.destinasi_id}`"
                                class="inline-flex min-h-9 items-center gap-1.5 font-semibold text-utama-teks hover:underline"
                            >
                                {{ t('Jelajahi destinasi', 'Explore destination') }}<ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </article>
                </div>
                <p v-if="!events.length" class="rounded-2xl border border-dashed border-garis p-8 text-center text-sm text-tinta-redup">{{ t('Belum ada agenda yang ditampilkan.', 'No events are currently listed.') }}</p>
                <details class="mt-5 text-xs leading-relaxed text-tinta-redup">
                    <summary class="cursor-pointer font-semibold text-utama-teks">{{ t('Tentang agenda & sumber foto', 'About these events & photo sources') }}</summary>
                    <p class="mt-2 max-w-[90ch]">{{ t('Agenda dan tarif pada halaman ini bersumber dari jadwal pariwisata resmi daerah. Foto dokumentasi resmi menampilkan keindahan lokasi, kuliner khas, dan festival budaya Sulawesi Tengah.', 'Events and tariffs on this page are sourced from regional tourism schedules. Official documentation photos feature destinations, culinary arts, and cultural festivals of Central Sulawesi.') }}</p>
                    <ul class="mt-2 space-y-1"><li v-for="photo in eventPhotos" :key="photo.src"><a :href="photo.source" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">{{ isEn ? photo.altEn : photo.altId }} — {{ photo.credit }}</a></li></ul>
                </details>
            </div>

            <!-- Section 2: Berita & Artikel Resmi (FR-16) -->
            <div id="berita" class="scroll-mt-24 border-t border-garis pt-8">
                <h2 class="font-heading mb-2 text-2xl font-bold text-tinta">
                    {{ t('Berita & Cerita Pariwisata', 'Tourism News & Stories') }}
                </h2>
                <p class="mb-6 max-w-[72ch] text-sm leading-relaxed text-tinta-redup">
                    {{ t('Panduan perjalanan, cerita warisan budaya, dan pembaruan layanan pariwisata Sulawesi Tengah.', 'Travel guides, cultural heritage stories, and updates on tourism services in Central Sulawesi.') }}
                </p>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <article
                        v-for="brt in beritaList"
                        :key="brt.id"
                        class="flex flex-col overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs"
                    >
                        <div>
                            <div class="relative aspect-[16/9] overflow-hidden bg-navy">
                                <img
                                    v-if="newsPhoto(brt)"
                                    :src="newsPhoto(brt).src"
                                    :alt="isEn ? newsPhoto(brt).altEn : newsPhoto(brt).altId"
                                    :style="{ objectPosition: newsPhoto(brt).position || 'center' }"
                                    loading="lazy"
                                    class="h-full w-full"
                                    :class="newsPhoto(brt).application ? 'object-contain bg-[#FAF6EE] p-2' : 'object-cover'"
                                />
                                <div v-else class="flex h-full flex-col items-center justify-center gap-2 text-sm text-white/80"><Newspaper class="h-8 w-8" />{{ t('Foto belum tersedia', 'Photo not yet available') }}</div>
                                <span class="absolute top-3 left-3 px-2.5 py-1 rounded bg-[#12344D]/90 text-white text-[11px] font-bold">
                                    {{ categoryLabel(brt.kategori_berita) }}
                                </span>
                                <a v-if="newsPhoto(brt)?.source" :href="newsPhoto(brt).source" target="_blank" rel="noopener noreferrer" class="absolute right-3 bottom-3 max-w-[90%] rounded bg-[#0B1F2E]/85 px-2 py-1 text-[10px] text-white">{{ t('Foto:', 'Photo:') }} {{ newsPhoto(brt).credit }}</a>
                                <span v-else-if="newsPhoto(brt)?.application" class="absolute right-3 bottom-3 rounded bg-[#0B1F2E]/85 px-2 py-1 text-[10px] text-white">{{ t('Tampilan aplikasi Jelajah Sulteng', 'Jelajah Sulteng app screenshot') }}</span>
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-tinta-redup">
                                    <time :datetime="String(brt.tanggal_terbit).slice(0, 10)" class="tabular-nums">{{ formatDate(brt.tanggal_terbit) }}</time>
                                    <span aria-hidden="true" class="text-garis-kuat">/</span>
                                    <span>{{ brt.wilayah?.nama_wilayah || t('Sulawesi Tengah', 'Central Sulawesi') }}</span>
                                </div>
                                <h3 class="font-heading text-lg font-bold leading-snug text-tinta sm:text-xl">
                                    <button type="button" @click="openArticle(brt)" class="cursor-pointer text-left transition hover:text-utama-teks focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-utama">{{ articleTitle(brt) }}</button>
                                </h3>
                                <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-tinta-redup">
                                    {{ isEn ? (brt.ringkasan_en || brt.ringkasan_id) : brt.ringkasan_id }}
                                </p>
                            </div>
                        </div>
                        <div class="mx-5 mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-garis py-4 sm:mx-6">
                            <div class="min-w-0 text-xs text-tinta-redup">
                                <p class="font-medium">{{ brt.penulis }}</p>
                                <span class="mt-1 inline-flex items-center gap-1 tabular-nums"><Eye class="h-3.5 w-3.5" />{{ formatNumber(brt.views_count) }} {{ t('dibaca', 'views') }}</span>
                            </div>
                            <button type="button" @click="openArticle(brt)" :aria-label="`${t('Baca selengkapnya:', 'Read more:')} ${articleTitle(brt)}`" class="inline-flex min-h-11 shrink-0 cursor-pointer items-center gap-2 rounded-xl bg-utama-muda px-3 text-xs font-semibold text-utama-teks transition hover:bg-utama hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-utama">{{ t('Baca selengkapnya', 'Read more') }}<ArrowRight class="h-4 w-4" /></button>
                        </div>
                    </article>
                </div>
                <p v-if="!beritaList.length" class="rounded-2xl border border-dashed border-garis p-8 text-center text-sm text-tinta-redup">{{ t('Belum ada berita yang ditampilkan.', 'No articles are currently listed.') }}</p>
                <p class="mt-4 text-xs leading-relaxed text-tinta-redup">{{ t('Artikel menggunakan data contoh untuk demonstrasi sistem. Foto pendamping tidak menyatakan keterlibatan pihak yang ditampilkan.', 'Articles use sample data for system demonstration. Supporting photographs do not imply involvement of the parties shown.') }}</p>
            </div>

            <section id="pengaduan" aria-labelledby="pengaduan-title" class="scroll-mt-24 border-t border-garis pt-8">
                <div class="mb-6">
                    <h2 id="pengaduan-title" class="font-heading text-2xl font-bold text-tinta">{{ t('Saran & Pengaduan Wisata', 'Tourism Feedback & Reports') }}</h2>
                    <p class="mt-2 max-w-[72ch] text-sm leading-relaxed text-tinta-redup">{{ t('Bantu tingkatkan kenyamanan wisata. Sampaikan kendala di destinasi dan lihat perkembangan tindak lanjutnya.', 'Help improve the travel experience. Report issues at destinations and follow their progress.') }}</p>
                </div>
                <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12 xl:gap-8">
                    <form @submit.prevent="submitPengaduan" class="complaint-form space-y-5 rounded-2xl border border-garis bg-permukaan p-5 shadow-xs sm:p-6 xl:col-span-5" aria-labelledby="laporan-form-title">
                        <div class="flex items-start gap-3 border-b border-garis pb-5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-utama-muda text-utama-teks"><MessageSquareWarning class="h-5 w-5" /></span>
                            <div>
                                <h3 id="laporan-form-title" class="font-heading text-lg font-bold text-tinta">{{ t('Kirim masukan Anda', 'Share your feedback') }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-tinta-redup">{{ t('Isi formulir berikut. Kolom bertanda * wajib diisi.', 'Complete the form below. Fields marked * are required.') }}</p>
                            </div>
                        </div>
                        <div v-if="reportSent" role="status" class="flex items-start gap-2 rounded-xl border border-sukses/30 bg-sukses/10 p-3 text-sm text-tinta"><CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-sukses" />{{ t('Laporan berhasil dikirim. Terima kasih atas masukan Anda.', 'Your report has been submitted. Thank you for your feedback.') }}</div>
                        <p v-if="Object.keys(pengaduanForm.errors).length" role="alert" class="rounded-xl border border-bahaya/30 bg-bahaya/10 p-3 text-sm text-bahaya">{{ t('Laporan belum terkirim. Periksa kembali kolom yang ditandai.', 'Your report was not submitted. Please check the marked fields.') }}</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="laporan-nama" class="complaint-label">{{ t('Nama pelapor', 'Your name') }} <span aria-hidden="true">*</span></label>
                                <input id="laporan-nama" v-model="pengaduanForm.nama_pelapor" type="text" autocomplete="name" required maxlength="120" :aria-invalid="!!pengaduanForm.errors.nama_pelapor" :aria-describedby="pengaduanForm.errors.nama_pelapor ? 'laporan-nama-error' : undefined" class="complaint-input" />
                                <p v-if="pengaduanForm.errors.nama_pelapor" id="laporan-nama-error" class="complaint-error">{{ pengaduanForm.errors.nama_pelapor }}</p>
                            </div>
                            <div>
                                <label for="laporan-email" class="complaint-label">{{ t('Email', 'Email') }} <span aria-hidden="true">*</span></label>
                                <input id="laporan-email" v-model="pengaduanForm.email_pelapor" type="email" autocomplete="email" inputmode="email" required maxlength="150" :aria-invalid="!!pengaduanForm.errors.email_pelapor" :aria-describedby="pengaduanForm.errors.email_pelapor ? 'laporan-email-error' : undefined" class="complaint-input" />
                                <p v-if="pengaduanForm.errors.email_pelapor" id="laporan-email-error" class="complaint-error">{{ pengaduanForm.errors.email_pelapor }}</p>
                            </div>
                        </div>
                        <div>
                            <label for="laporan-destinasi" class="complaint-label">{{ t('Destinasi terkait', 'Related destination') }} <span class="font-normal text-tinta-redup">{{ t('(opsional)', '(optional)') }}</span></label>
                            <select id="laporan-destinasi" v-model="pengaduanForm.destinasi_id" :aria-invalid="!!pengaduanForm.errors.destinasi_id" :aria-describedby="pengaduanForm.errors.destinasi_id ? 'laporan-destinasi-error' : undefined" class="complaint-input">
                                <option value="">{{ t('Umum / seluruh provinsi', 'General / province-wide') }}</option>
                                <option v-for="destination in destinasiOptions" :key="destination.id" :value="destination.id">{{ destination.nama_destinasi }}</option>
                            </select>
                            <p v-if="pengaduanForm.errors.destinasi_id" id="laporan-destinasi-error" class="complaint-error">{{ pengaduanForm.errors.destinasi_id }}</p>
                        </div>
                        <div>
                            <label for="laporan-kategori" class="complaint-label">{{ t('Kategori laporan', 'Report category') }} <span aria-hidden="true">*</span></label>
                            <select id="laporan-kategori" v-model="pengaduanForm.kategori" required :aria-invalid="!!pengaduanForm.errors.kategori" :aria-describedby="pengaduanForm.errors.kategori ? 'laporan-kategori-error' : undefined" class="complaint-input">
                                <option v-for="category in complaintCategories" :key="category" :value="category">{{ categoryLabel(category) }}</option>
                            </select>
                            <p v-if="pengaduanForm.errors.kategori" id="laporan-kategori-error" class="complaint-error">{{ pengaduanForm.errors.kategori }}</p>
                        </div>
                        <div>
                            <label for="laporan-judul" class="complaint-label">{{ t('Judul laporan', 'Report subject') }} <span aria-hidden="true">*</span></label>
                            <input id="laporan-judul" v-model="pengaduanForm.judul" type="text" required maxlength="160" :placeholder="t('Contoh: Tempat sampah penuh di area pantai', 'Example: Overflowing bins at the beach')" :aria-invalid="!!pengaduanForm.errors.judul" :aria-describedby="pengaduanForm.errors.judul ? 'laporan-judul-error' : undefined" class="complaint-input" />
                            <p v-if="pengaduanForm.errors.judul" id="laporan-judul-error" class="complaint-error">{{ pengaduanForm.errors.judul }}</p>
                        </div>
                        <div>
                            <label for="laporan-pesan" class="complaint-label">{{ t('Ceritakan kendalanya', 'Describe the issue') }} <span aria-hidden="true">*</span></label>
                            <textarea id="laporan-pesan" v-model="pengaduanForm.pesan" rows="5" required maxlength="2000" :placeholder="t('Tuliskan lokasi, waktu kejadian, dan kondisi yang Anda temukan.', 'Include the location, when it happened, and what you observed.')" :aria-invalid="!!pengaduanForm.errors.pesan" aria-describedby="laporan-pesan-help laporan-pesan-error" class="complaint-input resize-y"></textarea>
                            <div id="laporan-pesan-help" class="mt-2 flex justify-between gap-3 text-xs text-tinta-redup"><span>{{ t('Maksimal 2.000 karakter.', 'Up to 2,000 characters.') }}</span><span class="shrink-0 tabular-nums">{{ formatNumber(pengaduanForm.pesan.length) }} / {{ formatNumber(2000) }}</span></div>
                            <p v-if="pengaduanForm.errors.pesan" id="laporan-pesan-error" class="complaint-error">{{ pengaduanForm.errors.pesan }}</p>
                        </div>
                        <button type="submit" :disabled="pengaduanForm.processing" :aria-busy="pengaduanForm.processing" class="inline-flex min-h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-utama px-4 text-sm font-semibold text-white transition hover:bg-utama-gelap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-utama disabled:cursor-wait disabled:opacity-60 dark:text-[#0B1F2E]"><Send class="h-4 w-4" />{{ pengaduanForm.processing ? t('Mengirim laporan?', 'Sending report?') : t('Kirim laporan', 'Submit report') }}</button>
                    </form>
                    <div class="space-y-4 xl:col-span-7" aria-labelledby="tindak-lanjut-title">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 id="tindak-lanjut-title" class="font-heading text-lg font-bold text-tinta">{{ t('Perkembangan laporan', 'Report updates') }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-tinta-redup">{{ t('Status terbaru dan tanggapan dari Dinas Pariwisata.', 'Latest statuses and responses from the Tourism Office.') }}</p>
                            </div>
                            <span class="rounded-lg border border-garis bg-permukaan px-3 py-1.5 text-xs font-medium text-tinta-redup">{{ formatNumber(pengaduanList.length) }} {{ t('laporan terbaru', 'recent reports') }}</span>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2 border-b border-garis pb-4 text-xs text-tinta-redup" :aria-label="t('Tahapan tindak lanjut', 'Follow-up stages')">
                            <span class="inline-flex items-center gap-1.5"><CircleDot class="h-3.5 w-3.5 text-emas-teks" />{{ t('Diterima', 'Received') }}</span><ArrowRight class="h-3.5 w-3.5 text-tinta-redup" aria-hidden="true" /><span class="inline-flex items-center gap-1.5"><Clock3 class="h-3.5 w-3.5 text-utama" />{{ t('Diproses', 'In progress') }}</span><ArrowRight class="h-3.5 w-3.5 text-tinta-redup" aria-hidden="true" /><span class="inline-flex items-center gap-1.5"><CheckCircle2 class="h-3.5 w-3.5 text-sukses" />{{ t('Selesai', 'Resolved') }}</span>
                        </div>
                        <ol class="space-y-4" :aria-label="t('Daftar laporan masyarakat', 'Community reports')">
                            <li v-for="report in pengaduanList" :key="report.id">
                                <article class="space-y-4 rounded-2xl border border-garis bg-permukaan p-5 shadow-xs sm:p-6">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-mono text-xs font-semibold tabular-nums text-tinta-redup">{{ report.kode_pengaduan }}</span>
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold" :class="complaintStatus(report.status).classes"><component :is="complaintStatus(report.status).icon" class="h-3.5 w-3.5" />{{ complaintStatus(report.status).label }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-utama-teks">{{ categoryLabel(report.kategori) }}</p>
                                        <h4 class="mt-2 text-base font-bold leading-snug text-tinta">{{ report.judul }}</h4>
                                        <p v-if="report.destinasi" class="mt-2 flex items-center gap-1.5 text-xs text-tinta-redup"><MapPin class="h-3.5 w-3.5 shrink-0" />{{ report.destinasi.nama_destinasi }}</p>
                                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-relaxed text-tinta-redup">{{ report.pesan }}</p>
                                    </div>
                                    <div v-if="report.tanggapan_admin" class="rounded-xl border-l-2 border-utama bg-utama-muda/40 p-4">
                                        <p class="flex items-center gap-1.5 text-xs font-semibold text-utama-teks"><MessageSquareWarning class="h-3.5 w-3.5" />{{ t('Tanggapan Dinas Pariwisata', 'Tourism Office response') }}</p>
                                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-tinta">{{ report.tanggapan_admin }}</p>
                                        <p v-if="report.ditanggapi_pada" class="mt-2 text-xs text-tinta-redup">{{ formatDate(report.ditanggapi_pada) }}</p>
                                    </div>
                                    <p v-else class="border-t border-garis pt-3 text-xs text-tinta-redup">{{ t('Belum ada tanggapan yang dipublikasikan.', 'No response has been published yet.') }}</p>
                                </article>
                            </li>
                        </ol>
                        <div v-if="!pengaduanList.length" class="rounded-2xl border border-dashed border-garis bg-permukaan p-8 text-center"><MessageSquareWarning class="mx-auto mb-3 h-7 w-7 text-utama" /><h4 class="font-semibold text-tinta">{{ t('Belum ada laporan', 'No reports yet') }}</h4><p class="mt-2 text-sm text-tinta-redup">{{ t('Laporan masyarakat akan ditampilkan di sini.', 'Community reports will appear here.') }}</p></div>
                    </div>
                </div>
            </section>
        </section>
        <dialog
            ref="articleDialog"
            aria-labelledby="article-title"
            @close="onArticleClosed"
            @click="onArticleBackdropClick"
            class="fixed inset-0 m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-3xl overflow-y-auto rounded-2xl border border-garis bg-permukaan p-0 text-tinta shadow-2xl backdrop:bg-[#0B1F2E]/75"
        >
            <article v-if="activeArticle">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-garis bg-permukaan px-5 py-3">
                    <span class="text-xs font-semibold text-utama-teks">{{ categoryLabel(activeArticle.kategori_berita) }}</span>
                    <button type="button" autofocus @click="closeArticle" :aria-label="t('Tutup artikel', 'Close article')" class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-full border border-garis hover:bg-utama-muda"><X class="h-5 w-5" /></button>
                </div>
                <img v-if="newsPhoto(activeArticle)" :src="newsPhoto(activeArticle).src" :alt="isEn ? newsPhoto(activeArticle).altEn : newsPhoto(activeArticle).altId" class="max-h-96 w-full object-contain bg-permukaan-redup" />
                <div class="space-y-5 p-5 sm:p-8">
                    <h2 id="article-title" class="font-heading text-2xl font-bold leading-tight sm:text-3xl">{{ articleTitle(activeArticle) }}</h2>
                    <p class="text-xs leading-relaxed text-tinta-redup">{{ formatDate(activeArticle.tanggal_terbit) }} · {{ activeArticle.penulis }}</p>
                    <div class="space-y-4 text-base leading-relaxed">
                        <p v-for="(paragraph, index) in articleParagraphs" :key="index">{{ paragraph }}</p>
                    </div>
                    <div v-if="newsPhoto(activeArticle)" class="border-t border-garis pt-4 text-xs leading-relaxed text-tinta-redup">
                        <p>{{ isEn ? newsPhoto(activeArticle).altEn : newsPhoto(activeArticle).altId }}</p>
                        <a v-if="newsPhoto(activeArticle).source" :href="newsPhoto(activeArticle).source" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-1 text-utama-teks underline underline-offset-2">{{ t('Sumber foto:', 'Photo source:') }} {{ newsPhoto(activeArticle).credit }}<ExternalLink class="h-3 w-3" /></a>
                    </div>
                </div>
            </article>
        </dialog>
    </AppLayout>
</template>

<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import { ref, computed, nextTick, onBeforeUnmount } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Newspaper, MessageSquareWarning, CalendarDays, MapPin, ArrowRight, ExternalLink, Eye, X, CheckCircle2, CircleDot, Clock3, Send } from 'lucide-vue-next';
import AppLayout from '../Layouts/AppLayout.vue';
import { useAppContext } from '../composables/useAppContext';

defineProps({
    events: { type: Array, default: () => [] },
    beritaList: { type: Array, default: () => [] },
    destinasiOptions: { type: Array, default: () => [] },
    pengaduanList: { type: Array, default: () => [] },
});

const { t, isEn, user, formatCurrency, formatDate, formatNumber } = useAppContext();

const eventPhotos = {
    togean: {
        src: '/images/kadidiri-togean.jpg',
        position: 'center 65%',
        altId: 'Dermaga dan perairan Pulau Kadidiri di Kepulauan Togean',
        altEn: 'Jetty and coastal waters of Kadidiri Island in the Togean Islands',
        credit: 'Travelingyuk',
        source: 'https://travelingyuk.com/pulau-kadidiri-di-sulawesi-tengah/88800/',
    },
    poso: {
        src: '/images/festival-danau-poso.jpg',
        altId: 'Tari Kamberu pada pembukaan Festival Danau Poso 2022',
        altEn: 'Kamberu dance at the opening of the 2022 Lake Poso Festival',
        credit: 'ANTARA / Basri Marzuki',
        source: 'https://mediaindonesia.com/galleries/detail_galleries/27021-pembukaan-festival-danau-poso',
    },
    tumbe: {
        src: '/images/malabot-tumbe.jpg',
        position: 'center 70%',
        altId: 'Perahu prosesi Festival Malabot Tumbe 2024',
        altEn: 'Boats in the procession of the 2024 Malabot Tumbe Festival',
        credit: 'Dispar Banggai Kepulauan',
        source: 'https://pariwisata.banggaikep.go.id/festival-malabot-tumbe/',
    },
    palu: {
        src: '/images/kaledo-palu.jpg',
        altId: 'Kaledo, hidangan khas Palu dan Donggala',
        altEn: 'Kaledo, a traditional dish from Palu and Donggala',
        credit: 'Crisco1492 / DIGITANI IPB',
        source: 'https://digitani.ipb.ac.id/kaledo-olahan-sapi-legendaris-dari-sulawesi-tengah/',
    },
    lindu: {
        src: '/images/danau-tambing.jpg',
        altId: 'Perkemahan di tepi Danau Tambing, Taman Nasional Lore Lindu',
        altEn: 'Lakeside camping at Lake Tambing in Lore Lindu National Park',
        credit: 'detikTravel',
        source: 'https://travel.detik.com/cerita-perjalanan/d-5394667/danau-tambing-pesona-alam-di-taman-nasional-lore-lindu',
    },
};

const eventPhoto = (event) => {
    if (event.foto_banner) return { src: event.foto_banner, altId: event.nama_event_id, altEn: event.nama_event_en || event.nama_event_id };
    const name = `${event.slug || ''} ${event.nama_event_id || ''}`.toLowerCase();
    if (name.includes('togean')) return eventPhotos.togean;
    if (name.includes('poso')) return eventPhotos.poso;
    if (name.includes('tumbe')) return eventPhotos.tumbe;
    if (name.includes('palu') || name.includes('kaledo')) return eventPhotos.palu;
    if (name.includes('lindu') || name.includes('tambing')) return eventPhotos.lindu;
    return null;
};

const newsPhotos = {
    hotel: {
        src: '/images/hotel-palu.jpg',
        altId: 'Resepsionis Swiss-Belhotel Silae Palu, foto pendamping akomodasi',
        altEn: 'Reception at Swiss-Belhotel Silae Palu, supporting accommodation photograph',
        credit: 'Swiss-Belhotel Silae Palu',
        source: 'https://www.swiss-belhotel.com/gallery/swiss-belhotel-silae-palu',
    },
    analytics: {
        src: '/images/statistik-jelajah-sulteng.png',
        altId: 'Tangkapan layar statistik pariwisata pada aplikasi Jelajah Sulteng',
        altEn: 'Tourism statistics screenshot from the Jelajah Sulteng application',
        application: true,
    },
    heritage: {
        src: '/images/palindo-lembah-bada.jpg',
        position: 'center 25%',
        altId: 'Patung Palindo di Lembah Bada, foto Neni Puji Artanti',
        altEn: 'Palindo statue in Bada Valley, photographed by Neni Puji Artanti',
        credit: 'Neni Puji Artanti / KPKNL Palu',
        source: 'https://www.djkn.kemenkeu.go.id/kpknl-palu/baca-artikel/16571/Merenda-Sejarah-di-Lembah-Bada.html',
    },
};

const newsPhoto = (article) => {
    if (article.foto_utama) return { src: article.foto_utama, altId: article.judul_id, altEn: article.judul_en || article.judul_id };
    const name = `${article.slug || ''} ${article.judul_id || ''}`.toLowerCase();
    if (/(hotel|phri|menginap)/.test(name)) return newsPhotos.hotel;
    if (/(dashboard|analitik|data-pariwisata)/.test(name)) return newsPhotos.analytics;
    if (/(megalit|bada|palindo)/.test(name)) return newsPhotos.heritage;
    return eventPhoto({ nama_event_id: article.judul_id });
};

const activeArticle = ref(null);
const articleDialog = ref(null);
let previousBodyOverflow = '';
const articleTitle = (article) => isEn.value ? (article.judul_en || article.judul_id) : article.judul_id;
const articleParagraphs = computed(() => {
    if (!activeArticle.value) return [];
    const article = activeArticle.value;
    const content = isEn.value ? (article.isi_en || article.isi_id) : article.isi_id;
    return String(content || (isEn.value ? article.ringkasan_en || article.ringkasan_id : article.ringkasan_id) || '').split(/\n\s*\n/).filter(Boolean);
});

const openArticle = async (article) => {
    activeArticle.value = article;
    await nextTick();
    if (!articleDialog.value.open) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        articleDialog.value.showModal();
        articleDialog.value.scrollTop = 0;
    }
};
const closeArticle = () => articleDialog.value?.close();
const onArticleClosed = () => {
    document.body.style.overflow = previousBodyOverflow;
    activeArticle.value = null;
};
const onArticleBackdropClick = (event) => {
    if (event.target === articleDialog.value) closeArticle();
};
onBeforeUnmount(() => {
    if (activeArticle.value) document.body.style.overflow = previousBodyOverflow;
});

const categoryLabel = (category) => t(category, {
    'Bahari & Olahraga': 'Marine & Sports',
    'Festival Budaya': 'Cultural Festival',
    'Upacara Adat': 'Traditional Ceremony',
    'Kuliner & UMKM': 'Food & Local Business',
    'Ekonomi Kreatif': 'Creative Economy',
    'Siaran Pers': 'Press Release',
    'Panduan Wisata': 'Travel Guide',
    'Pengembangan Destinasi': 'Destination Development',
    'Kabar Event': 'Event News',
    'Kebersihan & Sanitasi': 'Cleanliness & Sanitation',
    'Fasilitas & Keamanan': 'Facilities & Safety',
    'Aksesibilitas Jalan': 'Road Accessibility',
    'Tarif & Tiket': 'Prices & Tickets',
    'Saran Pengembangan': 'Development Suggestions',
    Kebersihan: 'Cleanliness',
    Aksesibilitas: 'Accessibility',
    Fasilitas: 'Facilities',
}[category] || category);

const complaintCategories = ['Kebersihan & Sanitasi', 'Fasilitas & Keamanan', 'Aksesibilitas Jalan', 'Tarif & Tiket', 'Saran Pengembangan'];
const complaintStatus = (status) => ({
    baru: { label: t('Diterima', 'Received'), icon: CircleDot, classes: 'border-emas/30 bg-emas/10 text-emas-teks dark:text-emas' },
    diproses: { label: t('Diproses', 'In progress'), icon: Clock3, classes: 'border-utama/30 bg-utama-muda/50 text-utama-teks' },
    selesai: { label: t('Selesai', 'Resolved'), icon: CheckCircle2, classes: 'border-sukses/30 bg-sukses/10 text-sukses dark:text-[#6BC5B8]' },
}[status] || { label: status, icon: CircleDot, classes: 'border-garis bg-permukaan-redup text-tinta-redup' });
const reportSent = ref(false);
const pengaduanForm = useForm({
    nama_pelapor: user.value?.name || '',
    email_pelapor: user.value?.email || '',
    destinasi_id: '',
    kategori: 'Kebersihan & Sanitasi',
    judul: '',
    pesan: '',
});

function submitPengaduan() {
    reportSent.value = false;
    pengaduanForm.post('/pengaduan', {
        preserveScroll: true,
        onSuccess: () => {
            reportSent.value = true;
            pengaduanForm.judul = '';
            pengaduanForm.pesan = '';
        },
    });
}
</script>

<style scoped>
.complaint-label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--warna-teks);
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.5;
}

.complaint-input {
    display: block;
    width: 100%;
    min-height: 3rem;
    border: 1px solid var(--warna-garis-kuat);
    border-radius: 0.75rem;
    background: var(--warna-permukaan-redup);
    padding: 0.75rem;
    color: var(--warna-teks);
    font-size: 1rem;
    line-height: 1.5;
}

.complaint-input::placeholder {
    color: var(--warna-teks-redup);
    opacity: 1;
}

.complaint-input:focus-visible {
    border-color: var(--warna-utama);
    outline: 2px solid var(--warna-utama);
    outline-offset: 2px;
}

.complaint-input[aria-invalid='true'] {
    border-color: var(--warna-bahaya);
}

.complaint-error {
    margin-top: 0.5rem;
    color: var(--warna-bahaya);
    font-size: 0.875rem;
    line-height: 1.5;
}
</style>

