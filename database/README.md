# Direktori Database (Database Architecture & Backups)

Folder ini berisi seluruh komponen skema, migrasi, data awal (*seeding*), dan arsip SQL database aplikasi dan Data Warehouse Jelajah Sulteng.

---

## Struktur Direktori

```text
database/
├── factories/          # Laravel Model Factories
├── migrations/         # Skema tabel operasional dan tabel warehouse
├── seeders/            # DatabaseSeeder & ETL initialization
├── data/               # File dataset yang dibaca langsung oleh seeder
│   ├── dataset_pariwisata_sulteng.xlsx
│   ├── pariwisata_resmi_2025.csv
│   └── perhotelan_resmi_pilihan.csv
└── sql_dumps/          # Backup lengkap struktur & data SQL (MySQL 8.4)
    ├── jelajah_sulteng_app.sql  # Database operasional Laravel
    └── jelajah_sulteng_dw.sql   # Database analitik Star Schema Data Warehouse
```
