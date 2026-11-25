# PRD Jelajah Sulteng

Sistem Informasi dan Dashboard Analitik Pariwisata Sulawesi Tengah

Versi 1.0, November 2026. Studi kasus: Dinas Pariwisata Provinsi Sulawesi Tengah. Mata kuliah: Big Data Analysis (tugas akhir).

Nama "Jelajah Sulteng" adalah nama kerja dan bisa diganti kapan saja.

---

## 1. Ringkasan

Jelajah Sulteng adalah website resmi pariwisata Sulawesi Tengah untuk wisatawan, sekaligus sistem pengelolaan dan analitik untuk Dinas Pariwisata. Wisatawan bisa menjelajahi destinasi, menyusun itinerari, memesan tiket secara simulasi, dan menulis ulasan. Admin dinas dan pengelola destinasi memakai dashboard berisi 30 chart untuk memantau kunjungan, membaca prediksi, dan menerima rekomendasi tindakan.

Sistem dibangun dengan Laravel, MySQL, dan Vue. Data dikumpulkan di database operasional, dipindahkan secara berkala ke data warehouse, lalu dianalisis oleh sebuah layanan Python kecil yang menghasilkan prediksi kunjungan, skor destinasi unggulan, dan hasil analisis sentimen.

Satu hal yang perlu jujur sejak awal. Data real pariwisata Sulteng yang terbuka berbentuk agregat dan jumlah barisnya kecil. Karena itu sebagian besar data pada proyek ini adalah simulasi yang dikalibrasi dengan beberapa angka real dari BPS. Hal ini harus ditulis terang di laporan dan di halaman tentang sistem.

## 2. Latar belakang dan masalah

Sulawesi Tengah punya destinasi yang sangat beragam, dari Kepulauan Togean, Danau Poso, Taman Nasional Lore Lindu, sampai ikon kota seperti Jembatan Palu IV. Namun informasi untuk wisatawan tersebar di banyak tempat, dan data untuk pengambil keputusan sering hanya berupa tabel tahunan atau bulanan yang sulit dibaca.

Masalah yang ingin dijawab proyek ini:

1. Wisatawan kesulitan menemukan destinasi yang cocok, memperkirakan waktu terbaik berkunjung, dan menyusun perjalanan.
2. Dinas belum punya gambaran cepat tentang destinasi mana yang unggul, mana yang menurun, dan kapan kunjungan memuncak.
3. Keluhan wisatawan di ulasan tidak terkumpul dan tidak diubah menjadi tindakan perbaikan.
4. Keputusan promosi dan alokasi anggaran masih berdasar perkiraan, bukan prediksi dari data historis.

## 3. Tujuan dan ukuran keberhasilan

Tujuan proyek ada tiga. Pertama, menyediakan website wisatawan yang lengkap dan interaktif dalam dua bahasa. Kedua, menyediakan dashboard analitik dengan 30 chart yang bisa dipakai untuk mengambil keputusan. Ketiga, menunjukkan bahwa insight dari data bisa diterjemahkan menjadi tindakan lewat fitur rekomendasi aksi.

Ukuran keberhasilan yang dipakai:

| Ukuran | Target |
|---|---|
| Jumlah chart di sistem | 30 chart utama dalam Dashboard Admin, dikelompokkan menurut konteks wisatawan, pengelola, dan dinas |
| Akurasi prediksi kunjungan bulanan | MAPE di bawah 15 persen pada data uji |
| Model sentimen | F1 makro di atas 0,75 pada data uji |
| Waktu muat dashboard admin | Di bawah 3 detik dengan data simulasi |
| Alur utama berjalan penuh | Registrasi, pesan tiket, check-in, ulasan, sampai muncul di dashboard |
| Flow diagram | 25 diagram terdokumentasi |
| Rekomendasi aksi | Minimal 6 aturan aktif yang menghasilkan saran nyata dari data |

Catatan penting: angka akurasi di atas diukur pada data simulasi, jadi hanya membuktikan bahwa pipeline bekerja. Klaim tentang kondisi pariwisata Sulteng yang sebenarnya hanya boleh memakai data real.

## 4. Pengguna dan peran

Sistem memakai tiga peran akun, ditambah pengunjung yang belum login.

**Pengunjung (belum login).** Boleh melihat beranda, destinasi, peta, statistik publik, berita, dan event. Tidak bisa memesan tiket, menulis ulasan, atau menyimpan wishlist.

**User (wisatawan).** Pengunjung yang sudah mendaftar. Bisa wishlist, itinerari, pesan tiket, ulasan, chatbot, dan melihat riwayatnya sendiri.

**Pengelola destinasi.** Orang yang mengurus satu atau beberapa destinasi. Mengisi data kunjungan harian, memverifikasi tiket, membalas ulasan, dan melihat dashboard khusus destinasinya. Perubahan data penting seperti harga tiket harus disetujui admin.

**Admin dinas.** Pemegang kendali penuh. Mengelola seluruh data dan pengguna, menjalankan ETL dan model, membaca dashboard lengkap, dan menindaklanjuti rekomendasi aksi. Pimpinan dinas cukup memakai akun admin dengan halaman ringkasan eksekutif, tidak perlu peran tersendiri.

Admin tidak digantikan oleh pengelola destinasi. Keduanya punya tugas berbeda. Pengelola hanya melihat dan mengubah destinasinya sendiri, sedangkan admin melihat seluruh provinsi.

## 5. Cakupan

Masuk cakupan versi ini: seluruh fitur pada bagian 6, data warehouse, layanan analitik Python, 30 chart, dua bahasa, mode terang dan gelap, serta deploy ke link publik.

Tidak masuk cakupan versi ini: pembayaran sungguhan (hanya simulasi), aplikasi mobile native, chatbot berbasis model bahasa besar (dijadwalkan untuk tahap lanjutan), integrasi langsung ke sistem BPS atau pemerintah, dan notifikasi lewat WhatsApp.

## 6. Kebutuhan fungsional

Prioritas: W berarti wajib, P berarti penting, O berarti opsional jika waktu cukup.

### 6.1 Pengunjung dan User

| Kode | Fitur | Penjelasan | Prioritas |
|---|---|---|---|
| FR-01 | Beranda | Banner, destinasi unggulan, ringkasan statistik, event terdekat | W |
| FR-02 | Jelajah destinasi | Daftar dengan pencarian, filter kategori, kabupaten, rating, harga, dan urutan | W |
| FR-03 | Detail destinasi | Deskripsi, galeri, jam buka, harga, ulasan, indikator keramaian, destinasi terdekat | W |
| FR-04 | Peta wisata interaktif | Peta dengan penanda per kategori, pengelompokan penanda, filter, dan kartu info | W |
| FR-05 | Akun | Registrasi, login, lupa kata sandi, ubah profil dan bahasa | W |
| FR-06 | Wishlist | Simpan dan hapus destinasi favorit, halaman daftar wishlist | W |
| FR-07 | Perencana itinerari | Susun perjalanan per hari, atur urutan dengan seret dan lepas, hitung estimasi biaya tiket, ekspor ke PDF | W |
| FR-08 | Rating dan ulasan | Skor keseluruhan dan lima aspek (pemandangan, kebersihan, akses, fasilitas, harga) plus teks | W |
| FR-09 | Pemesanan tiket simulasi | Pilih tanggal dan jumlah, bayar simulasi QRIS atau transfer, terbit e-tiket dengan kode QR | W |
| FR-10 | Riwayat tiket | Daftar tiket, status, dan pembatalan sebelum tanggal kunjungan | W |
| FR-11 | Chatbot rekomendasi | Versi sederhana berbasis aturan, lihat bagian 7.5 | W |
| FR-12 | Dua bahasa | Indonesia dan Inggris, ganti tanpa muat ulang halaman | W |
| FR-13 | Indikator keramaian | Label sepi, sedang, ramai per destinasi berdasarkan prediksi dan data terbaru | P |
| FR-14 | Statistik publik | Tujuh chart ringan di halaman statistik | W |
| FR-15 | Kalender event | Event wisata per bulan dengan lokasi dan tanggal | P |
| FR-16 | Berita dan artikel | Daftar dan detail artikel dari dinas | P |
| FR-17 | Kotak saran dan pengaduan | Formulir dengan kategori dan pelacakan status | P |
| FR-18 | Mode gelap | Pilihan tema terang, gelap, atau mengikuti perangkat | P |
| FR-19 | Rekomendasi destinasi serupa | Saran destinasi berdasarkan kategori dan skor kemiripan | O |

### 6.2 Pengelola destinasi

| Kode | Fitur | Penjelasan | Prioritas |
|---|---|---|---|
| FR-20 | Dashboard destinasi | Lima chart tentang destinasi miliknya | W |
| FR-21 | Input kunjungan harian | Formulir harian dan impor CSV, dengan validasi | W |
| FR-22 | Kelola profil destinasi | Ubah deskripsi, foto, jam buka. Perubahan harga menunggu persetujuan admin | W |
| FR-23 | Verifikasi tiket | Input atau pindai kode tiket untuk check-in | W |
| FR-24 | Balas ulasan | Tanggapan resmi pengelola pada ulasan | P |
| FR-25 | Kelola event destinasi | Ajukan event yang tampil di kalender setelah disetujui | O |

### 6.3 Admin dinas

| Kode | Fitur | Penjelasan | Prioritas |
|---|---|---|---|
| FR-30 | Dashboard eksekutif | Enam belas chart analitik dengan filter periode, kabupaten, dan kategori | W |
| FR-31 | Kelola pengguna dan peran | Tambah, nonaktifkan, ubah peran, tetapkan destinasi untuk pengelola | W |
| FR-32 | Kelola master data | Destinasi, kategori, wilayah | W |
| FR-33 | Persetujuan perubahan | Antrean perubahan dari pengelola dengan setuju atau tolak | W |
| FR-34 | Impor data | Unggah Excel atau CSV dengan pratinjau dan laporan kesalahan | W |
| FR-35 | Manajemen ETL | Jalankan manual, lihat jadwal dan log tiap proses | W |
| FR-36 | Manajemen model | Lihat hasil prediksi, metrik akurasi, jalankan ulang model | W |
| FR-37 | Pusat rekomendasi aksi | Daftar insight otomatis dengan status tindak lanjut | W |
| FR-38 | Moderasi | Ulasan dan pengaduan yang dilaporkan atau bermasalah | P |
| FR-39 | Kelola event dan berita | Tambah dan ubah konten publik | P |
| FR-40 | Ekspor laporan | PDF ringkasan dan Excel data dari dashboard | P |
| FR-41 | Log aktivitas | Catatan tindakan penting pengguna admin dan pengelola | O |

## 7. Analisis Big Data dan pengambilan keputusan

Proyek ini memenuhi ketiga syarat utama tugas. Analisisnya dipakai untuk keputusan yang bisa dijalankan, memanfaatkan data historis untuk prediksi, dan mengubah insight menjadi tindakan.

### 7.1 Prediksi kunjungan wisatawan

Target prediksi adalah total kunjungan bulanan per destinasi dan untuk seluruh provinsi, enam bulan ke depan. Model yang dibandingkan adalah SARIMAX, Prophet, dan Holt-Winters. Variabel tambahan yang dipakai adalah penanda musim libur sekolah dan bulan Lebaran. Pemilihan model memakai validasi deret waktu (time series split) dengan ukuran MAPE dan RMSE. Hasil disimpan beserta batas bawah dan batas atas agar chart bisa menampilkan interval.

### 7.2 Pemetaan destinasi unggulan

Setiap destinasi diberi skor 0 sampai 100 dari lima komponen yang sudah dinormalkan: volume kunjungan, pertumbuhan, rating, sentimen ulasan, dan pendapatan tiket. Bobot awalnya 30, 20, 20, 15, dan 15 persen, dan admin boleh mengubahnya. Selain skor, destinasi dipetakan ke empat kuadran memakai kunjungan dan rating, lalu hasilnya dicocokkan dengan K-Means untuk memastikan kelompoknya masuk akal.

| Kuadran | Ciri | Arah tindakan |
|---|---|---|
| Unggulan | Kunjungan tinggi, rating tinggi | Jaga kualitas, tambah kapasitas pada hari ramai |
| Potensial | Kunjungan rendah, rating tinggi | Prioritas promosi dan paket perjalanan |
| Perlu pemasaran ulang | Kunjungan tinggi, rating rendah | Perbaiki layanan dan fasilitas sebelum promosi |
| Perlu evaluasi | Kunjungan rendah, rating rendah | Kaji ulang pengembangan dan alokasi anggaran |

### 7.3 Analisis sentimen ulasan

Teks ulasan dibersihkan, lalu diklasifikasikan menjadi positif, netral, atau negatif. Model awal memakai TF-IDF dengan Logistic Regression yang dilatih dari label pada dataset, dengan pembanding berupa pendekatan leksikon. Selain label, tiap ulasan dipetakan ke lima aspek lewat pencocokan kata kunci sehingga dashboard bisa menunjukkan aspek apa yang paling sering dikeluhkan. Peningkatan ke model berbasis IndoBERT dicatat sebagai pengembangan lanjutan.

### 7.4 Rekomendasi aksi

Hasil analisis diubah menjadi saran tindakan oleh mesin aturan. Setiap saran punya tingkat prioritas dan status tindak lanjut (baru, diproses, selesai, ditolak), sehingga dashboard tidak berhenti pada grafik.

| Kondisi yang terdeteksi | Saran tindakan |
|---|---|
| Prediksi kunjungan destinasi turun lebih dari 15 persen dari periode sama tahun lalu | Siapkan paket promo dan event pada bulan terdampak |
| Sentimen negatif pada aspek kebersihan di atas 25 persen | Jadwalkan inspeksi dan tambah petugas kebersihan |
| Keramaian akhir pekan rata-rata di atas 75 persen | Batasi kuota tiket per jam dan tambah petugas |
| Destinasi masuk kuadran Potensial | Masukkan ke kampanye promosi bulan berikutnya |
| TPK hotel di kabupaten di bawah 35 persen padahal kunjungan tinggi | Dorong paket menginap bersama pelaku akomodasi |
| Konversi dari itinerari ke pesan tiket di bawah 50 persen | Sederhanakan alur pemesanan dan periksa kendala pembayaran |

### 7.5 Chatbot rekomendasi (versi sederhana)

Versi awal memakai aturan, bukan model bahasa. Pesan pengguna dicocokkan dengan kamus kata kunci untuk mengenali niat (minta rekomendasi, tanya harga, tanya jam buka, tanya lokasi) dan entitas (kategori, kabupaten, anggaran, jumlah hari). Jawaban diambil dari database dan ditampilkan sebagai kartu destinasi, dengan kalimat cadangan jika pesan tidak dikenali. Tahap berikutnya bisa mengganti mesin aturan ini dengan model bahasa yang membaca data destinasi.

## 8. Katalog 30 chart berbasis data resmi

Buka **Statistik** pada portal atau **Statistik Resmi** pada Dashboard Admin. Grafik perjalanan memakai 312 pasangan indikator, bulan, dan wilayah dalam [CSV rekap Dinas Pariwisata 2025](database/pariwisata_resmi_2025.csv). Satu nilai pada tabel sumber bertanda strip disimpan sebagai kosong. Perjalanan wisnus dan kunjungan wisman memiliki satuan sumber masing-masing dan disajikan dengan filter indikator. Grafik hotel memakai tiga bulan publikasi BPS tingkat provinsi dari [CSV perhotelan](database/perhotelan_resmi_pilihan.csv). Tautan penerbit dan unduhan CSV tersedia di dashboard.

| Nomor | Jenis chart | Cakupan |
|---|---|---|
| 1 | Garis | Kabupaten/kota, 2025 |
| 2 | Kolom divergen | Kabupaten/kota, 2025 |
| 3 | Donut | Kabupaten/kota, 2025 |
| 4 | Batang horizontal | Kabupaten/kota, 2025 |
| 5 | Pareto | Kabupaten/kota, 2025 |
| 6 | Heatmap | Kabupaten/kota, 2025 |
| 7 | Area kumulatif | Kabupaten/kota, 2025 |
| 8 | Rata-rata bergerak | Kabupaten/kota, 2025 |
| 9 | Treemap | Kabupaten/kota, 2025 |
| 10 | Lollipop | Kabupaten/kota, 2025 |
| 11 | Box plot | Kabupaten/kota, 2025 |
| 12 | Histogram | Kabupaten/kota, 2025 |
| 13 | Dumbbell | Kabupaten/kota, 2025 |
| 14 | Batang divergen | Kabupaten/kota, 2025 |
| 15 | Slope chart | Kabupaten/kota, 2025 |
| 16 | Kolom frekuensi | Kabupaten/kota, 2025 |
| 17 | Batang rentang | Kabupaten/kota, 2025 |
| 18 | Scatter plot | Kabupaten/kota, 2025 |
| 19 | Waffle | Kabupaten/kota, 2025 |
| 20 | Radar | Kabupaten/kota, 2025 |
| 21 | Polar area | Kabupaten/kota, 2025 |
| 22 | Area bertumpuk | Kabupaten/kota, 2025 |
| 23 | Batang bertumpuk 100% | Kabupaten/kota, 2025 |
| 24 | Gauge radial | Kabupaten/kota, 2025 |
| 25 | Waterfall | Kabupaten/kota, 2025 |
| 26 | Bubble | Kabupaten/kota, 2025 |
| 27 | Kolom | Hotel bintang tingkat provinsi, bulan publikasi pilihan |
| 28 | Kolom bertumpuk | Hotel bintang tingkat provinsi, bulan publikasi pilihan |
| 29 | Dot plot persentase | Hotel bintang tingkat provinsi, bulan publikasi pilihan |
| 30 | Kolom berkelompok | Hotel bintang tingkat provinsi, bulan publikasi pilihan |

Grafik menggunakan data pada tingkat kabupaten/kota atau provinsi sesuai publikasi. Data operasional per destinasi berasal dari catatan pengelola atau aktivitas pengguna. Proyeksi dan skor lama yang berbasis data simulasi dinonaktifkan. Catatan audit historis tersedia di [SUMBER_DATA.md](SUMBER_DATA.md).

## 9. Data dan data warehouse

### 9.1 Sumber data

| Sumber | Dipakai untuk | Status |
|---|---|---|
| Portal Satu Data Sulteng, kunjungan wisatawan per kabupaten dan kota | Kerangka kunjungan real | Real, tabel kecil, perlu diunduh manual |
| BPS Sulteng, TPK hotel bintang dan tamu menginap | Kalibrasi data hotel | Real, hanya beberapa angka yang sudah dipakai |
| Databoks, jumlah kamar hotel bintang 2025 | Kalibrasi jumlah kamar | Real, satu angka tahunan |
| Dinas Pariwisata Sulteng, definisi operasional indikator | Rumus dan istilah indikator | Dokumen acuan |
| Dataset Excel proyek ini | Kunjungan, ulasan, tiket, dan funnel | Simulasi, dikalibrasi dengan angka real di atas |

Berkas dataset ada di dataset_pariwisata_sulteng.xlsx dengan kamus data lengkap di dalamnya.

### 9.2 Skema bintang

Database dipisah menjadi dua, yaitu database aplikasi (operasional) dan database warehouse (analitik). Proses ETL memindahkan data dari yang pertama ke yang kedua.

| Tabel warehouse | Jenis | Grain | Isi utama |
|---|---|---|---|
| dim_waktu | Dimensi | Satu baris per hari | Tahun, kuartal, bulan, hari, akhir pekan, musim libur |
| dim_destinasi | Dimensi | Satu baris per destinasi | Nama, kategori, harga, koordinat |
| dim_wilayah | Dimensi | Satu baris per kabupaten atau kota | Kode BPS, nama, koordinat |
| fact_kunjungan_harian | Fakta | Destinasi per hari | Wisnus, wisman, total, keramaian |
| fact_kunjungan_bulanan | Fakta | Destinasi per bulan | Wisnus, wisman, pendapatan tiket |
| fact_hotel_bulanan | Fakta | Wilayah per bulan | Kamar, TPK, tamu domestik dan asing |
| fact_asal_pengunjung | Fakta | Asal per jenis per bulan | Jumlah pengunjung |
| fact_ulasan | Fakta | Satu baris per ulasan | Rating, lima skor aspek, sentimen |
| fact_tiket | Fakta | Satu baris per tiket | Jumlah, total bayar, metode, status |
| fact_funnel_bulanan | Fakta | Tahap per bulan | Jumlah pengguna |

Tabel hasil analitik (hasil_prediksi, hasil_sentimen, skor_destinasi, rekomendasi_aksi) disimpan di warehouse dan dibaca langsung oleh dashboard.

### 9.3 Alur data

1. Data masuk lewat aplikasi (kunjungan harian, tiket, ulasan, aktivitas pengguna) atau lewat impor Excel oleh admin.
2. Perintah terjadwal Laravel menjalankan ETL setiap hari pada dini hari. ETL bersifat inkremental berdasarkan waktu perubahan, dan setiap eksekusi dicatat di tabel etl_runs.
3. Setelah ETL selesai, Laravel memanggil layanan Python untuk memperbarui prediksi, sentimen, dan skor destinasi.
4. Hasilnya ditulis ke warehouse dan mesin aturan membuat rekomendasi aksi baru.
5. Dashboard membaca dari tabel agregat di warehouse lewat endpoint JSON dan dicache untuk menjaga kecepatan.

## 10. Arsitektur dan teknologi

| Lapisan | Pilihan |
|---|---|
| Backend | Laravel dengan Sanctum atau Breeze untuk autentikasi, middleware peran untuk otorisasi |
| Database | MySQL 8, dua skema (aplikasi dan warehouse) |
| Frontend | Vue 3 dengan Inertia.js, vue-i18n untuk dua bahasa |
| Chart dan peta | ApexCharts, Leaflet dengan GeoJSON |
| Layanan analitik | Python dengan FastAPI, pandas, scikit-learn, statsmodels atau Prophet |
| Antrean dan jadwal | Laravel Scheduler dan queue (driver database) |
| Ekspor | DomPDF untuk PDF, Laravel Excel untuk Excel |
| Pengembangan lokal | Laragon atau Docker Compose |
| Deploy | Ditentukan kemudian, kandidat VPS kecil atau layanan hosting gratis dengan batasan |

Laravel tetap menjadi pintu tunggal bagi browser. Layanan Python tidak dibuka ke publik dan hanya dipanggil dari Laravel.

## 11. Kebutuhan non-fungsional

Performa: halaman publik dimuat di bawah 2 detik pada koneksi biasa, dashboard di bawah 3 detik, dengan agregasi dikerjakan di warehouse, bukan di browser.

Keamanan: kata sandi di-hash, perlindungan CSRF, pembatasan percobaan login, validasi unggahan (tipe dan ukuran), otorisasi per peran dan per destinasi, dan tidak ada data pembayaran sungguhan yang disimpan.

Privasi: nama pengguna pada ulasan ditampilkan secara singkat, dan data pribadi tidak muncul di chart.

Aksesibilitas: kontras warna memenuhi WCAG AA, semua chart punya judul, deskripsi singkat, dan akses ke data dalam bentuk tabel.

Responsif: tampilan dirancang mulai dari layar ponsel, dashboard tetap bisa dibaca di tablet.

Kualitas: uji otomatis untuk ETL, aturan rekomendasi, dan alur tiket, serta uji manual untuk alur utama sebelum pengumpulan.

Dokumentasi: README instalasi, kamus data, dan panduan menjalankan ETL dan model.

## 12. Daftar 25 flow diagram

Detail setiap diagram akan dibuat di dokumen WORKFLOW. Daftar judulnya sebagai berikut.

| No | Flow | Pelaku |
|---|---|---|
| 1 | Registrasi akun | Pengunjung |
| 2 | Login dan pengalihan sesuai peran | Semua |
| 3 | Lupa dan atur ulang kata sandi | Semua |
| 4 | Pencarian dan filter destinasi | Pengunjung, User |
| 5 | Melihat detail destinasi dan indikator keramaian | Pengunjung, User |
| 6 | Penjelajahan peta wisata | Pengunjung, User |
| 7 | Menambah dan menghapus wishlist | User |
| 8 | Menyusun itinerari | User |
| 9 | Pemesanan tiket dan pembayaran simulasi | User |
| 10 | Pembatalan tiket | User |
| 11 | Check-in dan verifikasi tiket | Pengelola |
| 12 | Menulis rating dan ulasan | User |
| 13 | Moderasi ulasan | Admin |
| 14 | Percakapan dengan chatbot | Pengunjung, User |
| 15 | Input kunjungan harian | Pengelola |
| 16 | Perubahan profil destinasi dan persetujuan | Pengelola, Admin |
| 17 | Impor data Excel | Admin |
| 18 | Proses ETL ke data warehouse | Sistem |
| 19 | Prediksi kunjungan | Sistem |
| 20 | Analisis sentimen ulasan | Sistem |
| 21 | Penilaian dan pemetaan destinasi unggulan | Sistem |
| 22 | Pembuatan dan tindak lanjut rekomendasi aksi | Sistem, Admin |
| 23 | Membaca dashboard dan memakai filter | Admin, Pengelola |
| 24 | Ekspor laporan | Admin |
| 25 | Saran dan pengaduan | User, Admin |

## 13. Kesesuaian dengan ketentuan tugas

| Ketentuan | Pemenuhan di proyek ini |
|---|---|
| Studi kasus nyata dari sektor pemerintahan | Dinas Pariwisata Provinsi Sulawesi Tengah |
| Data real lebih baik, dummy boleh | Kerangka dari data BPS dan Satu Data Sulteng, selebihnya simulasi dengan kalibrasi dan dijelaskan terbuka |
| Dashboard berbasis web | Dashboard admin dan pengelola dalam website Laravel dan Vue |
| Boleh fullset website | Website wisatawan lengkap dengan wishlist, itinerari, tiket, ulasan, peta, chatbot |
| Syarat utama 1, keputusan yang bisa dijalankan | Pusat rekomendasi aksi dan pemetaan destinasi unggulan |
| Syarat utama 2, prediksi dari data historis | Prediksi kunjungan enam bulan ke depan |
| Syarat utama 3, insight menjadi aksi | Aturan yang mengubah hasil analisis menjadi tugas dengan status tindak lanjut |
| Output: dashboard berjalan lokal atau lewat link | Lokal dengan Laragon atau Docker, lalu deploy |
| Output: dataset dan sumber data | dataset_pariwisata_sulteng.xlsx dengan sheet ref_data_real berisi tautan sumber |
| Output: laporan atau PPT singkat | Dibuat belakangan, isi: latar belakang, proses analisis, insight, rekomendasi |
| Output: source code | Repositori proyek |

## 14. Rencana pengerjaan

Tenggat belum ditentukan, jadi rencana ini memakai asumsi enam minggu dan bisa digeser.

| Minggu | Fokus |
|---|---|
| 1 | Finalisasi dokumen (PRD, desain, ERD, workflow), setup proyek, migrasi database, seeder dari dataset Excel |
| 2 | Autentikasi dan peran, master data, jelajah dan detail destinasi, peta |
| 3 | Wishlist, itinerari, tiket simulasi, rating dan ulasan |
| 4 | Data warehouse dan ETL, dashboard pengelola, chart admin tahap pertama |
| 5 | Layanan Python (prediksi, sentimen, skor), rekomendasi aksi, chart admin sisanya, chatbot, dua bahasa |
| 6 | Pengujian, perbaikan, deploy, laporan atau PPT singkat, rekaman demo |

Fitur berprioritas O dikerjakan hanya jika jadwal aman. Pekerjaan paling berisiko, yaitu ETL dan layanan analitik, sengaja diletakkan di tengah agar ada ruang perbaikan.

## 15. Risiko dan penanganan

| Risiko | Dampak | Penanganan |
|---|---|---|
| Data real terbatas dan agregat | Analisis terlihat kurang meyakinkan | Gabungkan data real dan simulasi, jelaskan terbuka, siapkan skrip agar data real bisa menggantikan simulasi |
| Akurasi model hanya bermakna pada data simulasi | Klaim berlebihan di laporan | Tulis bahwa akurasi mengukur pipeline, bukan kondisi nyata |
| Cakupan terlalu besar untuk satu orang | Tidak selesai | Pegang prioritas W, kerjakan P dan O belakangan |
| GeoJSON batas wilayah sulit didapat | Dua peta choropleth gagal | Cari sumber lebih awal, cadangan berupa peta penanda berukuran sesuai nilai |
| Dashboard lambat | Pengalaman buruk saat demo | Agregasi di warehouse, cache, dan batas rentang waktu |
| Hosting gratis terbatas | Demo online tidak stabil | Siapkan demo lokal sebagai cadangan dan rekaman layar |
| Nama dan koordinat destinasi belum diverifikasi | Informasi keliru | Verifikasi dengan data resmi dinas sebelum dipublikasikan |

## 16. Pertanyaan yang masih terbuka

1. Tenggat pengumpulan dan jadwal presentasi.
2. Rencana hosting untuk deploy.
3. Apakah nama kerja Jelajah Sulteng dipakai atau diganti.
4. Sumber foto destinasi, apakah foto sendiri atau foto berlisensi bebas.
5. Apakah dosen meminta format laporan atau PPT tertentu.
