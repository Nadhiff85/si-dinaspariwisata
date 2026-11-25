<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations on the Data Warehouse connection.
     */
    public function up(): void
    {
        $schema = Schema::connection('warehouse');

        $schema->dropIfExists('rekomendasi_aksi');
        $schema->dropIfExists('skor_destinasi');
        $schema->dropIfExists('hasil_sentimen');
        $schema->dropIfExists('hasil_prediksi');
        $schema->dropIfExists('fact_funnel_bulanan');
        $schema->dropIfExists('fact_tiket');
        $schema->dropIfExists('fact_ulasan');
        $schema->dropIfExists('fact_asal_pengunjung');
        $schema->dropIfExists('fact_hotel_bulanan');
        $schema->dropIfExists('fact_kunjungan_bulanan');
        $schema->dropIfExists('fact_kunjungan_harian');
        $schema->dropIfExists('dim_destinasi');
        $schema->dropIfExists('dim_wilayah');
        $schema->dropIfExists('dim_waktu');

        $schema->create('dim_waktu', function (Blueprint $table) {
            $table->date('tanggal')->primary();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('kuartal');
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('nama_bulan', 30);
            $table->string('periode', 7)->index();
            $table->unsignedTinyInteger('hari_dalam_minggu');
            $table->string('nama_hari', 25);
            $table->string('akhir_pekan', 10)->index();
            $table->string('musim_libur_sekolah', 10)->index();
            $table->timestamps();
        });

        $schema->create('dim_wilayah', function (Blueprint $table) {
            $table->unsignedBigInteger('wilayah_id')->primary();
            $table->string('kode_bps', 10)->index();
            $table->string('nama_wilayah', 120);
            $table->string('jenis', 30);
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->timestamps();
        });

        $schema->create('dim_destinasi', function (Blueprint $table) {
            $table->unsignedBigInteger('destinasi_id')->primary();
            $table->string('nama_destinasi', 150);
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->string('nama_wilayah', 120)->nullable();
            $table->string('kategori', 60)->index();
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->unsignedBigInteger('harga_tiket_dewasa')->default(0);
            $table->unsignedBigInteger('harga_tiket_anak')->default(0);
            $table->string('jam_operasional', 60);
            $table->text('deskripsi_id');
            $table->text('deskripsi_en');
            $table->timestamps();
        });

        $schema->create('fact_kunjungan_harian', function (Blueprint $table) {
            $table->unsignedBigInteger('harian_id')->primary();
            $table->date('tanggal')->index();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->string('kategori', 60)->index();
            $table->unsignedInteger('wisnus')->default(0);
            $table->unsignedInteger('wisman')->default(0);
            $table->unsignedInteger('total_pengunjung')->default(0);
            $table->unsignedInteger('kapasitas_harian')->default(200);
            $table->decimal('keramaian_persen', 6, 2)->default(0);
            $table->string('tingkat_keramaian', 20)->index();
            $table->timestamps();
        });

        $schema->create('fact_kunjungan_bulanan', function (Blueprint $table) {
            $table->unsignedBigInteger('kunjungan_id')->primary();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->string('kategori', 60)->index();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('periode', 7)->index();
            $table->unsignedInteger('wisnus')->default(0);
            $table->unsignedInteger('wisman')->default(0);
            $table->unsignedInteger('total_pengunjung')->default(0);
            $table->unsignedBigInteger('pendapatan_tiket_rp')->default(0);
            $table->timestamps();
        });

        $schema->create('fact_hotel_bulanan', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->primary();
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('periode', 7)->index();
            $table->unsignedInteger('jumlah_hotel_bintang')->default(0);
            $table->unsignedInteger('jumlah_kamar_bintang')->default(0);
            $table->decimal('tpk_persen', 5, 2)->default(0);
            $table->unsignedInteger('tamu_domestik')->default(0);
            $table->unsignedInteger('tamu_asing')->default(0);
            $table->decimal('rata_lama_menginap_hari', 4, 2)->default(1.00);
            $table->timestamps();
        });

        $schema->create('fact_asal_pengunjung', function (Blueprint $table) {
            $table->unsignedBigInteger('asal_id')->primary();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('periode', 7)->index();
            $table->string('jenis_wisatawan', 20)->index();
            $table->string('asal', 100)->index();
            $table->unsignedInteger('jumlah')->default(0);
            $table->timestamps();
        });

        $schema->create('fact_ulasan', function (Blueprint $table) {
            $table->unsignedBigInteger('ulasan_id')->primary();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->string('kategori', 60)->index();
            $table->date('tanggal')->index();
            $table->string('periode', 7)->index();
            $table->string('nama_pengguna', 100);
            $table->string('bahasa', 5)->default('id');
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('skor_pemandangan')->default(4);
            $table->unsignedTinyInteger('skor_kebersihan')->default(4);
            $table->unsignedTinyInteger('skor_akses')->default(4);
            $table->unsignedTinyInteger('skor_fasilitas')->default(4);
            $table->unsignedTinyInteger('skor_harga')->default(4);
            $table->text('teks_ulasan');
            $table->string('label_sentimen', 20)->index();
            $table->string('user_id', 30)->nullable();
            $table->timestamps();
        });

        $schema->create('fact_tiket', function (Blueprint $table) {
            $table->unsignedBigInteger('tiket_id')->primary();
            $table->string('kode_tiket', 40)->unique();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->date('tanggal_pesan')->index();
            $table->date('tanggal_kunjungan')->index();
            $table->string('periode', 7)->index();
            $table->unsignedInteger('jumlah_dewasa')->default(1);
            $table->unsignedInteger('jumlah_anak')->default(0);
            $table->unsignedBigInteger('harga_dewasa')->default(0);
            $table->unsignedBigInteger('harga_anak')->default(0);
            $table->unsignedBigInteger('total_bayar_rp')->default(0);
            $table->string('metode_bayar', 40)->index();
            $table->string('status', 30)->index();
            $table->string('asal_pemesan', 30)->index();
            $table->string('user_id', 30)->nullable();
            $table->timestamps();
        });

        $schema->create('fact_funnel_bulanan', function (Blueprint $table) {
            $table->unsignedBigInteger('funnel_id')->primary();
            $table->string('periode', 7)->index();
            $table->unsignedTinyInteger('tahap_no');
            $table->string('tahap', 80);
            $table->unsignedInteger('jumlah_pengguna')->default(0);
            $table->timestamps();
        });

        $schema->create('hasil_prediksi', function (Blueprint $table) {
            $table->id();
            $table->string('cakupan', 30)->default('provinsi')->index();
            $table->unsignedBigInteger('destinasi_id')->nullable()->index();
            $table->string('periode', 7)->index();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedInteger('prediksi_wisnus')->default(0);
            $table->unsignedInteger('prediksi_wisman')->default(0);
            $table->unsignedInteger('prediksi_total')->default(0);
            $table->unsignedInteger('batas_bawah')->default(0);
            $table->unsignedInteger('batas_atas')->default(0);
            $table->unsignedInteger('aktual_total')->nullable();
            $table->string('model_terpilih', 40)->default('SARIMAX');
            $table->decimal('mape_persen', 5, 2)->default(8.42);
            $table->decimal('rmse', 10, 2)->default(142.50);
            $table->boolean('is_musim_libur')->default(false);
            $table->boolean('is_lebaran')->default(false);
            $table->timestamps();
        });

        $schema->create('hasil_sentimen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->string('periode', 7)->default('ALL')->index();
            $table->unsignedInteger('total_ulasan')->default(0);
            $table->unsignedInteger('positif_count')->default(0);
            $table->unsignedInteger('netral_count')->default(0);
            $table->unsignedInteger('negatif_count')->default(0);
            $table->decimal('persen_positif', 5, 2)->default(0);
            $table->decimal('persen_netral', 5, 2)->default(0);
            $table->decimal('persen_negatif', 5, 2)->default(0);
            $table->decimal('keluhan_kebersihan_persen', 5, 2)->default(0);
            $table->decimal('keluhan_akses_persen', 5, 2)->default(0);
            $table->decimal('keluhan_fasilitas_persen', 5, 2)->default(0);
            $table->decimal('keluhan_harga_persen', 5, 2)->default(0);
            $table->json('kata_kunci_negatif_json')->nullable();
            $table->string('model_digunakan', 60)->default('TF-IDF + Logistic Regression');
            $table->decimal('f1_macro', 5, 4)->default(0.8420);
            $table->timestamps();
        });

        $schema->create('skor_destinasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id')->unique();
            $table->string('periode_evaluasi', 7)->default('2026-10');
            $table->decimal('skor_volume', 5, 2)->default(0);
            $table->decimal('skor_pertumbuhan', 5, 2)->default(0);
            $table->decimal('skor_rating', 5, 2)->default(0);
            $table->decimal('skor_sentimen', 5, 2)->default(0);
            $table->decimal('skor_pendapatan', 5, 2)->default(0);
            $table->decimal('skor_total', 5, 2)->default(0)->index();
            $table->json('bobot_json')->nullable();
            $table->unsignedBigInteger('total_kunjungan_12bln')->default(0);
            $table->decimal('pertumbuhan_persen', 6, 2)->default(0);
            $table->decimal('rating_rata_rata', 3, 2)->default(0);
            $table->unsignedBigInteger('pendapatan_12bln_rp')->default(0);
            $table->string('kuadran', 40)->index();
            $table->unsignedTinyInteger('klaster_kmeans')->default(1);
            $table->string('arah_tindakan');
            $table->timestamps();
        });

        $schema->create('rekomendasi_aksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aturan', 30)->index();
            $table->string('judul_id', 180);
            $table->string('judul_en', 180);
            $table->unsignedBigInteger('destinasi_id')->nullable()->index();
            $table->unsignedBigInteger('wilayah_id')->nullable()->index();
            $table->string('prioritas', 20)->default('tinggi')->index();
            $table->string('kategori_aksi', 60)->default('Promosi & Paket');
            $table->text('kondisi_terdeteksi_id');
            $table->text('kondisi_terdeteksi_en');
            $table->text('saran_tindakan_id');
            $table->text('saran_tindakan_en');
            $table->string('data_pendukung');
            $table->string('metrik_nilai', 80)->nullable();
            $table->string('status', 25)->default('baru')->index();
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->unsignedBigInteger('ditindaklanjuti_oleh')->nullable();
            $table->timestamp('ditindaklanjuti_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('warehouse');
        $schema->dropIfExists('rekomendasi_aksi');
        $schema->dropIfExists('skor_destinasi');
        $schema->dropIfExists('hasil_sentimen');
        $schema->dropIfExists('hasil_prediksi');
        $schema->dropIfExists('fact_funnel_bulanan');
        $schema->dropIfExists('fact_tiket');
        $schema->dropIfExists('fact_ulasan');
        $schema->dropIfExists('fact_asal_pengunjung');
        $schema->dropIfExists('fact_hotel_bulanan');
        $schema->dropIfExists('fact_kunjungan_bulanan');
        $schema->dropIfExists('fact_kunjungan_harian');
        $schema->dropIfExists('dim_destinasi');
        $schema->dropIfExists('dim_wilayah');
        $schema->dropIfExists('dim_waktu');
    }
};

