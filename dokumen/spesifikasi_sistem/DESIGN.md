# Desain Jelajah Sulteng

Panduan desain antarmuka untuk website wisatawan dan dashboard analitik. Dokumen ini mengikuti PRD.md dan memakai palet Laut Togean.

---

## 1. Arah desain

Tampilan harus terasa seperti liburan di laut yang jernih, tetapi tetap bisa dipakai serius oleh staf dinas. Sisi wisatawan memakai foto besar, ruang kosong yang lega, dan warna laut yang segar. Sisi dashboard memakai tata letak yang rapat dan tenang agar chart yang berwarna jadi pusat perhatian.

Empat pegangan yang dipakai di seluruh layar:

1. Satu warna utama (teal laut) untuk aksi, aksen karang hanya untuk hal yang perlu menarik mata.
2. Setiap chart menjawab satu pertanyaan, dan judulnya ditulis sebagai pertanyaan atau kesimpulan.
3. Teks tidak pernah bergantung pada warna saja. Status selalu punya label atau ikon.
4. Rancang untuk layar ponsel dulu, baru diperlebar ke desktop.

## 2. Warna

### 2.1 Warna inti (mode terang)

| Token | Hex | Pemakaian |
|---|---|---|
| --warna-utama | #0E7C86 | Tombol utama, tautan pada latar putih, elemen aktif |
| --warna-utama-gelap | #0A5F67 | Teks teal pada latar pasir, status hover tombol |
| --warna-utama-muda | #D5EEF0 | Latar chip, sorotan lembut |
| --warna-navy | #12344D | Judul, teks utama, sidebar dashboard |
| --warna-aksen | #F26B4F | Ikon, lencana, bagian chart, tombol ajakan (teks navy di atasnya) |
| --warna-aksen-teks | #C2492E | Teks aksen karang pada latar terang |
| --warna-pasir | #F5EFE3 | Latar halaman |
| --warna-permukaan | #FFFFFF | Kartu dan panel |
| --warna-teks-redup | #556877 | Teks pendukung dan keterangan |
| --warna-garis | #E3DACB | Garis pemisah dan batas kartu |
| --warna-emas | #E0A422 | Peringatan ringan, bintang rating, status sedang |
| --warna-sukses | #2E8B57 | Status berhasil |
| --warna-bahaya | #C2492E | Status gagal dan hapus |

### 2.2 Warna inti (mode gelap)

| Token | Hex | Pemakaian |
|---|---|---|
| --warna-latar | #0B1F2E | Latar halaman |
| --warna-permukaan | #12344D | Kartu dan panel |
| --warna-utama | #4FC3CC | Tombol, tautan, elemen aktif (teks gelap di atasnya) |
| --warna-teks | #F5EFE3 | Teks utama |
| --warna-teks-redup | #9DB2C0 | Teks pendukung |
| --warna-aksen | #FF8A70 | Aksen karang |
| --warna-garis | #1F4A68 | Pemisah |

### 2.3 Rasio kontras yang sudah dihitung

| Pasangan | Rasio | Catatan |
|---|---|---|
| Putih di atas teal #0E7C86 | 4,95 | Lulus AA untuk teks tombol |
| Teal #0E7C86 di atas pasir #F5EFE3 | 4,32 | Tidak lulus untuk teks kecil, pakai #0A5F67 |
| Teal gelap #0A5F67 di atas pasir | 6,44 | Aman |
| Navy #12344D di atas pasir | 11,29 | Aman untuk semua teks |
| Karang #F26B4F di atas putih | 3,00 | Jangan dipakai untuk teks, hanya ikon dan bidang warna |
| Navy di atas karang #F26B4F | 4,30 | Aman untuk teks besar dan tebal pada tombol |
| Karang gelap #C2492E di atas putih | 4,90 | Aman untuk teks aksen |
| Teks redup #556877 di atas pasir | 5,05 | Aman |
| Pasir #F5EFE3 di atas #0B1F2E (gelap) | 14,67 | Aman |
| Teal #4FC3CC di atas #0B1F2E (gelap) | 8,01 | Aman |

### 2.4 Warna chart

Palet kategori (urutan pemakaian, delapan warna):

| Urutan | Hex | Nama |
|---|---|---|
| 1 | #0E7C86 | Teal laut |
| 2 | #12344D | Navy |
| 3 | #F26B4F | Karang |
| 4 | #E0A422 | Emas |
| 5 | #6BC5B8 | Hijau laut |
| 6 | #4C9BD6 | Biru langit |
| 7 | #6B7F8E | Abu baja |
| 8 | #8E5A7E | Ungu plum |

Aturan pemakaian: pakai maksimal enam seri pada satu chart. Bila lebih, kelompokkan sisanya sebagai "Lainnya". Pada mode gelap, ganti navy dengan #9DB2C0 agar tetap terlihat.

Skala berurutan (untuk heatmap dan peta choropleth, dari rendah ke tinggi): #E6F4F5, #B4DEE1, #6BC5B8, #1F9AA5, #0E7C86, #0A5F67.

Skala sentimen: positif #0E7C86, netral #D9CFB8, negatif #F26B4F. Skala ini selalu memakai label teks juga.

Skala keramaian: sepi #6BC5B8, sedang #E0A422, ramai #F26B4F, ditemani ikon dan kata.

Karena warna saja tidak cukup bagi pengguna buta warna, chart harus punya label langsung pada data penting, legenda yang jelas, dan tooltip berisi angka.

## 3. Tipografi

| Peran | Font | Cadangan |
|---|---|---|
| Judul | Plus Jakarta Sans, bobot 600 dan 700 | system-ui, sans-serif |
| Isi dan antarmuka | Inter, bobot 400, 500, 600 | system-ui, sans-serif |
| Angka pada tabel dan KPI | Inter dengan fitur angka tabular | ui-monospace untuk kode tiket |

Font dimuat dari berkas lokal (self-host) agar cepat dan tidak bergantung jaringan.

Skala ukuran (ponsel / desktop):

| Gaya | Ukuran | Tinggi baris | Bobot |
|---|---|---|---|
| Judul besar (hero) | 32 / 56 px | 1,15 | 700 |
| Judul bagian (H2) | 24 / 36 px | 1,2 | 700 |
| Judul kartu (H3) | 18 / 22 px | 1,3 | 600 |
| Isi | 16 px | 1,6 | 400 |
| Keterangan | 14 px | 1,5 | 400 |
| Label kecil | 12 px | 1,4 | 500 |
| Angka KPI | 28 / 36 px | 1,1 | 700 |

Format angka mengikuti locale: 1.250.000 dalam bahasa Indonesia dan 1,250,000 dalam bahasa Inggris. Tanggal ditulis 7 November 2026 atau 7 Nov 2026.

## 4. Grid, jarak, dan bentuk

| Elemen | Nilai |
|---|---|
| Titik pecah | 480 px (ponsel besar), 768 px (tablet), 1024 px (laptop), 1280 px (desktop) |
| Lebar isi maksimum | 1200 px untuk halaman publik, penuh untuk dashboard |
| Kolom | 4 kolom (ponsel), 8 (tablet), 12 (desktop), jarak antar kolom 16 dan 24 px |
| Skala jarak | 4, 8, 12, 16, 24, 32, 48, 64 px |
| Radius | 8 px (input, chip), 12 px (tombol), 16 px (kartu), 24 px (hero dan gambar besar) |
| Bayangan kartu | 0 1px 2px rgba(18,52,77,.06), 0 4px 16px rgba(18,52,77,.08) |
| Bayangan kartu terangkat | 0 8px 28px rgba(18,52,77,.16) |
| Garis | 1 px warna garis, tanpa bayangan pada tabel |

Potongan variabel CSS yang bisa dipakai langsung atau dipetakan ke konfigurasi Tailwind:

```css
:root {
  --warna-utama: #0E7C86;
  --warna-utama-gelap: #0A5F67;
  --warna-utama-muda: #D5EEF0;
  --warna-navy: #12344D;
  --warna-aksen: #F26B4F;
  --warna-aksen-teks: #C2492E;
  --warna-pasir: #F5EFE3;
  --warna-permukaan: #FFFFFF;
  --warna-teks: #12344D;
  --warna-teks-redup: #556877;
  --warna-garis: #E3DACB;
  --warna-emas: #E0A422;
  --warna-sukses: #2E8B57;
  --warna-bahaya: #C2492E;
  --radius-kartu: 16px;
  --radius-tombol: 12px;
}

:root[data-tema="gelap"] {
  --warna-pasir: #0B1F2E;
  --warna-permukaan: #12344D;
  --warna-utama: #4FC3CC;
  --warna-teks: #F5EFE3;
  --warna-teks-redup: #9DB2C0;
  --warna-aksen: #FF8A70;
  --warna-garis: #1F4A68;
}
```

## 5. Komponen

**Tombol utama.** Latar teal, teks putih, radius 12, tinggi 44 px (target sentuh). Hover memakai teal gelap. Fokus memakai cincin 3 px teal muda. Pada mode gelap teks berubah menjadi navy.

**Tombol sekunder.** Garis teal 1,5 px, teks teal gelap, latar transparan.

**Tombol ajakan (pesan tiket).** Latar karang, teks navy tebal. Dipakai hanya satu per layar.

**Kartu destinasi.** Foto rasio 4:3 dengan sudut atas membulat, nama destinasi, kabupaten, rating bintang dengan angka, chip kategori, dan lencana keramaian. Ikon hati di pojok foto untuk wishlist. Pada hover, foto sedikit membesar dan bayangan naik.

**Chip kategori.** Latar teal muda, teks teal gelap, ikon di kiri. Kategori Bahari, Alam, Budaya dan Sejarah, Religi, serta Ikon Kota masing-masing punya ikon sendiri.

**Lencana keramaian.** Titik berwarna, kata (Sepi, Sedang, Ramai), dan ikon orang. Selalu memakai teks.

**Kartu KPI.** Angka besar, label, dan perubahan dibanding periode lalu dengan panah dan persen. Panah naik berwarna hijau laut, panah turun berwarna karang gelap, keduanya dengan tanda plus atau minus.

**Kartu chart.** Judul (pertanyaan atau kesimpulan), subjudul periode, tombol menu (unduh PNG, lihat tabel data, layar penuh), area chart, dan satu baris catatan sumber data di bawah.

**Formulir.** Label di atas input, tinggi input 44 px, pesan galat berwarna karang gelap di bawah input dengan ikon, bukan hanya perubahan warna garis.

**Tabel.** Header berwarna navy muda, baris selang-seling sangat halus, angka rata kanan, dan paginasi. Pada ponsel tabel menjadi daftar kartu.

**Modal dan toast.** Modal punya judul, isi, dan dua tombol. Toast muncul di kanan bawah selama 4 detik dengan ikon status, dan bisa ditutup.

**Kondisi kosong, memuat, dan galat.** Kerangka (skeleton) abu hangat untuk memuat, ilustrasi sederhana dan satu kalimat petunjuk untuk kosong, serta tombol "Coba lagi" untuk galat.

## 6. Navigasi dan tata letak

### Halaman publik

Bilah atas berisi logo, menu (Beranda, Jelajah, Peta, Statistik, Event, Berita), pemilih bahasa ID dan EN, tombol tema, dan tombol Masuk atau menu profil. Pada ponsel menu menjadi laci dari kiri. Bilah atas tetap menempel di atas saat menggulir dengan latar semi-transparan.

Kaki halaman berisi tentang sistem (termasuk pernyataan bahwa sebagian data adalah simulasi), tautan, dan kontak dinas.

### Dashboard

Sidebar kiri berwarna navy dengan ikon dan teks, bisa diciutkan menjadi ikon saja. Di atas konten ada bilah filter global yang menempel: periode, kabupaten, dan kategori. Perubahan filter memperbarui semua chart di halaman. Pada ponsel sidebar menjadi menu laci dan filter menjadi lembar yang naik dari bawah.

Menu admin: Ringkasan, Kunjungan, Destinasi unggulan, Ulasan dan sentimen, Hotel dan asal wisatawan, Prediksi, Rekomendasi aksi, Data (impor dan ETL), Pengguna, Konten, Laporan.

Menu pengelola: Ringkasan, Kunjungan harian, Tiket dan check-in, Ulasan, Profil destinasi.

## 7. Wireframe

Wireframe berikut hanya memperlihatkan susunan, bukan tampilan akhir.

### Beranda

```
+--------------------------------------------------------------+
| Logo   Beranda Jelajah Peta Statistik Event   ID|EN  tema Masuk |
+--------------------------------------------------------------+
|                                                              |
|   [ foto besar laut Togean ]                                 |
|   Jelajahi Sulawesi Tengah                                   |
|   [ Cari destinasi.................. ] [Kategori v] [Cari]   |
|                                                              |
+--------------------------------------------------------------+
| Destinasi unggulan                                  Lihat semua |
| [kartu] [kartu] [kartu] [kartu]                              |
+--------------------------------------------------------------+
| Sulteng dalam angka                                          |
| [KPI kunjungan] [KPI destinasi] [KPI rating] [KPI event]    |
| [donut kategori]            [line tren 12 bulan]             |
+--------------------------------------------------------------+
| Event terdekat      | Berita terbaru                         |
+--------------------------------------------------------------+
| Kaki halaman                                                 |
+--------------------------------------------------------------+
```

### Jelajah destinasi

```
+--------------------------------------------------------------+
| Bilah atas                                                   |
+---------------+----------------------------------------------+
| Filter        | 27 destinasi   Urutkan: [Rating v]  [grid|peta]|
| Kategori      | [kartu] [kartu] [kartu]                      |
| [x] Bahari    | [kartu] [kartu] [kartu]                      |
| [ ] Alam      | [kartu] [kartu] [kartu]                      |
| Kabupaten [v] |                                              |
| Harga  o----o |            Paginasi                          |
| Rating  4+    |                                              |
+---------------+----------------------------------------------+
```

### Detail destinasi

```
+--------------------------------------------------------------+
| Galeri foto (satu besar, empat kecil)                        |
+------------------------------------------+-------------------+
| Nama destinasi    [hati wishlist]        | Kartu tiket       |
| Kabupaten - Kategori - rating 4,6 (120)  | Tanggal [......]  |
| Keramaian hari ini: [Sedang]             | Dewasa [-] 2 [+]  |
| Deskripsi                                | Anak   [-] 0 [+]  |
| Info: jam buka, harga, akses             | Total Rp 50.000   |
| [radar aspek]  [heatmap hari terbaik]    | [Pesan tiket]     |
| Ulasan (filter, urutkan, tulis ulasan)   | [Tambah itinerari]|
| Destinasi terdekat                       |                   |
+------------------------------------------+-------------------+
```

### Peta wisata

```
+--------------------------------------------------------------+
| Bilah atas                                                   |
+-------------+------------------------------------------------+
| Filter chip |                                                |
| Daftar hasil|        peta Leaflet dengan penanda             |
| [kartu mini]|        berwarna per kategori dan klaster       |
| [kartu mini]|                              [kartu info]      |
+-------------+------------------------------------------------+
```

### Perencana itinerari

```
+--------------------------------------------------------------+
| Itinerari saya: [Nama perjalanan]  Tanggal [..] - [..]       |
+------------------+-------------------------------------------+
| Wishlist         | Hari 1                                    |
| [kartu kecil]    |  1. Pantai Talise        09.00 [handle]   |
| [kartu kecil]    |  2. Jembatan Palu IV     16.00 [handle]   |
| [kartu kecil]    | Hari 2                                    |
|                  |  1. Pantai Tanjung Karang  ...            |
|                  | Estimasi biaya tiket: Rp 85.000           |
|                  | [Simpan] [Unduh PDF] [Pesan semua tiket]  |
+------------------+-------------------------------------------+
```

### Dashboard admin (Ringkasan)

```
+---------+----------------------------------------------------+
| Sidebar | Filter: [Periode v] [Kabupaten v] [Kategori v]     |
|         +----------------------------------------------------+
|         | [KPI kunjungan] [KPI wisman] [KPI TPK] [KPI rating]|
|         +-------------------------------+--------------------+
|         | Prediksi 6 bulan (line)       | Rekomendasi aksi   |
|         |                               | [!] Prioritas tinggi|
|         |                               | [!] Prioritas sedang|
|         +---------------+---------------+--------------------+
|         | Peta kunjungan| Treemap        | Donut asal wisman  |
|         +---------------+---------------+--------------------+
|         | Kuadran destinasi (bubble)   | Funnel pengguna    |
+---------+-------------------------------+--------------------+
```

### Dashboard pengelola

```
+---------+----------------------------------------------------+
| Sidebar | Destinasi: Pantai Talise                           |
|         | [KPI hari ini] [KPI bulan ini] [KPI rating]        |
|         | [line 30 hari]            [bar bulan vs tahun lalu]|
|         | [pie wisnus wisman] [bar tiket] [donut sentimen]   |
|         | [Input kunjungan hari ini]  [Verifikasi tiket]     |
+---------+----------------------------------------------------+
```

## 8. Pola chart dan dashboard

Setiap kartu chart memakai pola yang sama agar pengguna cepat paham. Judul berupa pertanyaan atau kesimpulan, misalnya "Kapan Togean paling ramai?" dan bukan "Grafik kunjungan". Satu kalimat insight otomatis boleh muncul di bawah chart, misalnya "Puncak kunjungan terjadi pada Juli".

Tooltip menampilkan nama seri, nilai dengan format locale, dan persentase bila relevan. Sumbu diberi satuan. Label sumbu yang panjang dipotong dengan elipsis dan lengkapnya muncul di tooltip. Chart yang bersifat pembagian (pie dan donut) maksimal enam irisan.

Setiap chart bisa dilihat sebagai tabel lewat menu kartu, yang sekaligus menjadi alternatif bagi pembaca layar. Ekspor PNG dan CSV tersedia di menu yang sama.

Peta choropleth memakai skala berurutan teal dengan legenda di pojok, dan klik pada wilayah membuka ringkasan angka wilayah itu.

Kartu rekomendasi aksi menampilkan tingkat prioritas (ikon dan kata), kalimat saran, data pendukung satu baris, dan tombol Ubah status.

## 9. Interaksi dan gerak

Durasi gerak 150 sampai 250 ms dengan easing lembut. Gerak dipakai untuk memberi tahu perubahan (kartu terangkat, chart tumbuh saat muncul, drawer meluncur), bukan sebagai hiasan. Semua gerak dimatikan atau dikurangi bila pengguna mengaktifkan prefers-reduced-motion.

Perubahan filter pada dashboard menampilkan kerangka singkat pada chart yang sedang dimuat, tanpa memuat ulang seluruh halaman. Aksi yang berhasil memunculkan toast. Aksi yang merusak (hapus, batalkan tiket) meminta konfirmasi lewat modal.

## 10. Dua bahasa

Semua teks antarmuka disimpan sebagai kunci terjemahan, bukan teks langsung di komponen. Pergantian bahasa berlaku seketika dan preferensinya disimpan di akun (atau di perangkat untuk pengunjung). Rancang ruang untuk teks bahasa Inggris yang umumnya sedikit lebih panjang. Konten dari database (deskripsi destinasi) punya dua kolom, satu untuk tiap bahasa, dan jika salah satu kosong akan memakai bahasa lainnya. Format angka, tanggal, dan mata uang mengikuti bahasa yang aktif.

## 11. Gambar dan ikon

Ikon memakai satu keluarga, yaitu Lucide, dengan ketebalan garis 1,75 px. Foto destinasi harus berupa foto sendiri atau foto berlisensi bebas dengan atribusi dicatat, bukan hasil unduhan acak. Selama foto belum ada, kartu memakai gradasi teal dengan ikon kategori sebagai pengganti. Setiap foto diberi teks alternatif, dikompres ke format WebP, dan dimuat malas (lazy load).

## 12. Aksesibilitas

1. Semua teks memenuhi kontras AA sesuai tabel pada bagian 2.3.
2. Seluruh fungsi bisa dioperasikan dengan papan ketik dan urutan fokus logis, dengan cincin fokus yang jelas.
3. Target sentuh minimal 44 x 44 px.
4. Gambar punya teks alternatif, chart punya judul, deskripsi, dan tabel data pengganti.
5. Status tidak disampaikan hanya lewat warna.
6. Formulir memakai label yang terhubung dan pesan galat yang dibacakan pembaca layar.
7. Bahasa halaman ditandai pada atribut lang dan berubah saat bahasa diganti.

## 13. Struktur frontend yang disarankan

```
resources/js/
  Pages/
    Beranda.vue
    Destinasi/ (Index.vue, Show.vue)
    Peta.vue
    Itinerari.vue
    Tiket/ (Pesan.vue, Riwayat.vue)
    Statistik.vue
    Admin/ (Ringkasan.vue, Kunjungan.vue, Unggulan.vue, Ulasan.vue,
            Prediksi.vue, Rekomendasi.vue, Data.vue, Pengguna.vue)
    Pengelola/ (Ringkasan.vue, Kunjungan.vue, Tiket.vue, Profil.vue)
  Components/
    ui/ (Tombol, Kartu, Chip, Lencana, Modal, Toast, Tabel, Form)
    destinasi/ (KartuDestinasi, GaleriFoto, BadgeKeramaian)
    chart/ (KartuChart, ChartGaris, ChartBar, ChartDonut, ChartHeatmap,
            ChartRadar, ChartTreemap, ChartBubble, ChartFunnel, PetaChoropleth)
    dashboard/ (BilahFilter, KartuKPI, KartuRekomendasi)
  composables/ (useTema, useBahasa, useFormatAngka, useFilterDashboard)
  lang/ (id.json, en.json)
```

Komponen chart dibuat pembungkus tipis di atas ApexCharts agar warna, font, tooltip, dan format angka seragam dan hanya didefinisikan satu kali.
