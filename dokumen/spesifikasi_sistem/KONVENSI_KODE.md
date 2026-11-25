# Konvensi Kode & Panduan Pengembangan Sistem

Dokumen ini merangkum hasil pemindaian arsitektur proyek (berdasarkan skill `infer-conventions`) serta panduan singkat deployment (berdasarkan skill `deploying-to-cloud`) agar konsistensi pengembangan tetap terjaga.

---

## 1. Stack & Versi Utama
* **Backend:** PHP 8.3+, Laravel 13 (`laravel/framework ^13.17`), Inertia.js (`inertiajs/inertia-laravel ^3.7`).
* **Frontend:** Vue 3 + Vite + Tailwind CSS + ApexCharts (`vue3-apexcharts`) + Lucide Icons.
* **Ekspor & Dokumen:** `barryvdh/laravel-dompdf` (PDF), `phpoffice/phpspreadsheet` (Excel).
* **Testing & Formatting:** PHPUnit 12 (`php artisan test`), Laravel Pint (`laravel/pint`).
* **Database Ganda (Dual MySQL):**
  * Koneksi default `mysql` → database operasional (`jelajah_sulteng_app`).
  * Koneksi `warehouse` → database Star Schema Data Warehouse (`jelajah_sulteng_dw`).

---

## 2. Arsitektur & Konvensi Kode (`app/`)
* **Lapisan Layanan (`app/Services/`):** Logika agregasi analitik dan sinkronisasi ETL ditempatkan di service class terpisah (`DashboardChartService`, `WarehouseEtlService`) yang di-inject ke controller/command.
* **Tanpa Repository Layer:** Controller (`AdminDashboardController`, `PengelolaDashboardController`, `PublicPortalController`, `TouristActionController`, `AuthController`) melakukan query Eloquent / `DB::connection('warehouse')` secara langsung atau melalui `Services`.
* **Validasi:** Validasi input dilakukan secara inline di controller (`$request->validate([...])`), tanpa `FormRequest` terpisah.
* **Model (`app/Models/`):**
  * Penamaan tabel & kolom menggunakan bahasa Indonesia (`destinasi`, `wilayah`, `kunjungan_bulanan`, `harga_tiket_dewasa`, dll.) dengan properti `$table` eksplisit jika tidak mengikuti plural Inggris.
  * Casting atribut menggunakan method `casts(): array` (konvensi Laravel 11+).
* **Autorisasi Peran:** Menggunakan middleware kustom `EnsureUserHasRole` untuk membedakan akses `admin`, `pengelola`, dan `wisatawan`.
* **Frontend (`resources/js/`):**
  * Halaman dirender melalui Inertia (`Inertia::render(...)`).
  * Komponen grafik resmi terpusat di `resources/js/Components/dashboard/OfficialTourismCharts.vue` dan kartu grafik reusable di `resources/js/Components/chart/KartuChart.vue`.
  * Dukungan dwibahasa (ID/EN) melalui composable `useAppContext()` dengan helper `t('Teks ID', 'Text EN')`.

---

## 3. Perintah Penting Pengembangan
* **Build Frontend:** `npm run build` (atau `npm run dev` saat pengembangan aktif).
* **Jalankan Test:** `php artisan test` (seluruh 25 test PHPUnit harus lulus sebelum push/deploy).
* **Sinkronisasi ETL Data Warehouse:** `php artisan etl:run`.
* **Format Kode PHP:** `./vendor/bin/pint`.

---

## 4. Ringkasan Panduan Deploy (Jika Nanti Deploy ke Laravel Cloud / Production)
* **Build Command:** `composer install --no-dev && npm run build`.
* **Deploy Command:** `php artisan migrate --force` (jangan jalankan `optimize:clear` atau `storage:link` di deploy command pada lingkungan ephemeral).
* **Environment & Rahasia:** Pastikan `.env` tidak pernah di-commit ke Git; konfigurasi koneksi `mysql` dan `warehouse` diatur melalui environment variables / secrets di server tujuan.
* **Storage:** Untuk lingkungan cloud/multi-replica, gunakan Object Storage (S3/R2) untuk file upload persisten dan database/Redis/Valkey untuk cache & session.
