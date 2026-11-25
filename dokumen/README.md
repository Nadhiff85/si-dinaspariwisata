# Direktori Dokumen & Sumber Resmi (Official Documents & Research Artifacts)

Folder ini mengelompokkan seluruh dokumen referensi resmi, dataset Excel terverifikasi, dokumen audit & validasi data, serta spesifikasi perancangan sistem pariwisata Sulawesi Tengah agar terpisah secara rapi dari folder inti sistem aplikasi (*clean architecture*).

---

## Struktur Direktori

```text
dokumen/
├── audit/                          # Khusus dokumen audit, validasi, dan pembuktian integritas data
│   ├── BUKTI_DATASET.html          # Portal bukti audit web lokal
│   ├── SUMBER_DATA.md              # Lembar audit SHA-256 dan verifikasi rujukan
│   ├── VALIDASI_DATA_EXCEL.xlsx    # Buku kerja hasil validasi & audit data Excel
│   └── bukti_dataset_sulteng.zip   # Arsip paket lengkap bukti audit dataset
│
├── sumber_pdf/                     # Salinan resmi dokumen PDF dari instansi berwenang
│   ├── sumber_kunjungan_sulteng_2025.pdf
│   ├── sumber_analisis_kunjungan_sulteng_2024.pdf
│   ├── sumber_analisis_kunjungan_sulteng_2023.pdf
│   ├── sumber_analisis_kunjungan_sulteng_2022.pdf
│   ├── sumber_perjalanan_provinsi_tujuan_jan_mar_2026.pdf
│   ├── sumber_statistik_akomodasi_bintang_2021_2025.pdf
│   ├── sumber_definisi_operasional_pariwisata.pdf
│   └── sumber_lppd_sulteng_2025.pdf
│
├── dataset_excel/                  # Buku kerja Excel dataset utama
│   ├── dataset_pariwisata_sulteng.xlsx
│   └── dataset_pariwisata_sulteng_dengan_sumber.xlsx
│
├── DATASET_RESMI_SULTENG_REAL/     # Paket instrumen presentasi & portal dataset resmi
│
└── spesifikasi_sistem/             # Dokumen arsitektur, PRD, dan panduan desain
    ├── PRD.md                      # Product Requirements Document
    └── DESIGN.md                   # Desain visual, tipografi, dan token UI
```

---

## Hubungan dengan Folder Database

Data yang digunakan oleh seeder aplikasi Laravel (`database/seeders/DatabaseSeeder.php`) tersimpan secara terisolasi pada:
* `database/data/dataset_pariwisata_sulteng.xlsx`
* `database/data/pariwisata_resmi_2025.csv`
* `database/data/perhotelan_resmi_pilihan.csv`
* `database/sql_dumps/jelajah_sulteng_app.sql` (Dump MySQL database operasional)
* `database/sql_dumps/jelajah_sulteng_dw.sql` (Dump MySQL Star Schema Data Warehouse)
