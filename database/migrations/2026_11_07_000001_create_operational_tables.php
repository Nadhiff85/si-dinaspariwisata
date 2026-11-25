<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('kode_bps', 10)->unique();
            $table->string('nama_wilayah', 120);
            $table->string('jenis', 30);
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->text('deskripsi_id')->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->timestamps();
        });

        Schema::create('destinasi', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('slug', 160)->unique();
            $table->string('nama_destinasi', 150);
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->string('kategori', 60)->index();
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->unsignedBigInteger('harga_tiket_dewasa')->default(0);
            $table->unsignedBigInteger('harga_tiket_anak')->default(0);
            $table->string('jam_operasional', 60)->default('08.00 - 17.00');
            $table->unsignedInteger('kapasitas_harian')->default(250);
            $table->text('deskripsi_id');
            $table->text('deskripsi_en');
            $table->json('fasilitas_json')->nullable();
            $table->json('galeri_json')->nullable();
            $table->string('foto_utama')->nullable();
            $table->decimal('rating_rata_rata', 3, 2)->default(4.50);
            $table->unsignedInteger('jumlah_ulasan')->default(0);
            $table->string('tingkat_keramaian_hari_ini', 20)->default('Sedang');
            $table->decimal('keramaian_persen_hari_ini', 6, 2)->default(50.00);
            $table->boolean('is_unggulan')->default(false);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->foreign('wilayah_id')->references('id')->on('wilayah')->cascadeOnDelete();
        });

        Schema::create('waktu_kalender', function (Blueprint $table) {
            $table->date('tanggal')->primary();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('kuartal');
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('nama_bulan', 30);
            $table->string('periode', 7)->index();
            $table->unsignedTinyInteger('hari_dalam_minggu');
            $table->string('nama_hari', 25);
            $table->string('akhir_pekan', 10);
            $table->string('musim_libur_sekolah', 10);
            $table->timestamps();
        });

        Schema::create('kunjungan_bulanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('periode', 7)->index();
            $table->unsignedInteger('wisnus')->default(0);
            $table->unsignedInteger('wisman')->default(0);
            $table->unsignedInteger('total_pengunjung')->default(0);
            $table->unsignedBigInteger('pendapatan_tiket_rp')->default(0);
            $table->string('sumber_data', 30)->default('excel_original');
            $table->timestamps();

            $table->unique(['destinasi_id', 'periode']);
            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('kunjungan_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedInteger('wisnus')->default(0);
            $table->unsignedInteger('wisman')->default(0);
            $table->unsignedInteger('total_pengunjung')->default(0);
            $table->unsignedInteger('kapasitas_harian')->default(200);
            $table->decimal('keramaian_persen', 6, 2)->default(0);
            $table->string('tingkat_keramaian', 20)->default('Sepi')->index();
            $table->unsignedBigInteger('diinput_oleh')->nullable();
            $table->string('catatan_operasional')->nullable();
            $table->string('sumber_data', 30)->default('excel_original')->index();
            $table->timestamps();

            $table->unique(['destinasi_id', 'tanggal']);
            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('hotel_bulanan', function (Blueprint $table) {
            $table->id();
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
            $table->string('sumber_data', 30)->default('excel_original');
            $table->timestamps();

            $table->unique(['wilayah_id', 'periode']);
            $table->foreign('wilayah_id')->references('id')->on('wilayah')->cascadeOnDelete();
        });

        Schema::create('asal_pengunjung', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedTinyInteger('bulan')->index();
            $table->string('periode', 7)->index();
            $table->string('jenis_wisatawan', 20)->index();
            $table->string('asal', 100)->index();
            $table->unsignedInteger('jumlah')->default(0);
            $table->string('sumber_data', 30)->default('excel_original');
            $table->timestamps();
        });

        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('kode_pengguna', 30)->nullable()->index();
            $table->date('tanggal')->index();
            $table->string('nama_pengguna', 100);
            $table->string('bahasa', 5)->default('id');
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('skor_pemandangan')->default(4);
            $table->unsignedTinyInteger('skor_kebersihan')->default(4);
            $table->unsignedTinyInteger('skor_akses')->default(4);
            $table->unsignedTinyInteger('skor_fasilitas')->default(4);
            $table->unsignedTinyInteger('skor_harga')->default(4);
            $table->text('teks_ulasan');
            $table->string('label_sentimen', 20)->default('positif')->index();
            $table->decimal('skor_sentimen', 5, 4)->default(0.7500);
            $table->text('balasan_pengelola')->nullable();
            $table->timestamp('dibalas_pada')->nullable();
            $table->unsignedBigInteger('dibalas_oleh')->nullable();
            $table->string('status_moderasi', 25)->default('tayang')->index();
            $table->string('alasan_lapor')->nullable();
            $table->string('sumber_data', 30)->default('excel_original')->index();
            $table->timestamps();

            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('tiket', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket', 40)->unique();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('kode_pengguna', 30)->nullable()->index();
            $table->string('nama_pemesan', 120)->nullable();
            $table->string('email_pemesan', 150)->nullable();
            $table->date('tanggal_pesan')->index();
            $table->date('tanggal_kunjungan')->index();
            $table->unsignedInteger('jumlah_dewasa')->default(1);
            $table->unsignedInteger('jumlah_anak')->default(0);
            $table->unsignedBigInteger('harga_dewasa')->default(0);
            $table->unsignedBigInteger('harga_anak')->default(0);
            $table->unsignedBigInteger('total_bayar_rp')->default(0);
            $table->string('metode_bayar', 40)->default('QRIS')->index();
            $table->string('status', 30)->default('Selesai')->index();
            $table->string('asal_pemesan', 30)->default('Wisnus')->index();
            $table->timestamp('waktu_checkin')->nullable();
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable();
            $table->string('sumber_data', 30)->default('excel_original')->index();
            $table->timestamps();

            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('funnel_bulanan', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->index();
            $table->unsignedTinyInteger('tahap_no');
            $table->string('tahap', 80);
            $table->unsignedInteger('jumlah_pengguna')->default(0);
            $table->string('sumber_data', 30)->default('excel_original');
            $table->timestamps();
        });

        Schema::create('ref_data_real', function (Blueprint $table) {
            $table->id();
            $table->string('indikator');
            $table->string('nilai');
            $table->string('periode', 60);
            $table->string('sumber');
            $table->text('url')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('kamus_data', function (Blueprint $table) {
            $table->id();
            $table->string('sheet', 60)->index();
            $table->string('kolom', 80);
            $table->string('tipe', 40);
            $table->text('keterangan');
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'destinasi_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('judul_perjalanan', 160);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedTinyInteger('jumlah_hari')->default(1);
            $table->unsignedTinyInteger('jumlah_dewasa')->default(2);
            $table->unsignedTinyInteger('jumlah_anak')->default(0);
            $table->unsignedBigInteger('estimasi_biaya_tiket_rp')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('itinerary_id')->index();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedTinyInteger('hari_ke')->default(1);
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->string('jam_mulai', 20)->default('09.00');
            $table->unsignedSmallInteger('durasi_menit')->default(120);
            $table->unsignedBigInteger('estimasi_biaya_rp')->default(0);
            $table->string('catatan_item')->nullable();
            $table->timestamps();

            $table->foreign('itinerary_id')->references('id')->on('itineraries')->cascadeOnDelete();
            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('nama_event_id', 160);
            $table->string('nama_event_en', 160);
            $table->unsignedBigInteger('wilayah_id')->index();
            $table->unsignedBigInteger('destinasi_id')->nullable()->index();
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_selesai')->index();
            $table->string('lokasi_detail', 160);
            $table->string('kategori_event', 60)->default('Festival Budaya');
            $table->text('deskripsi_id');
            $table->text('deskripsi_en');
            $table->unsignedBigInteger('harga_tiket_rp')->default(0);
            $table->string('status_persetujuan', 30)->default('disetujui')->index();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->foreign('wilayah_id')->references('id')->on('wilayah')->cascadeOnDelete();
        });

        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('judul_id', 180);
            $table->string('judul_en', 180);
            $table->text('ringkasan_id');
            $table->text('ringkasan_en');
            $table->longText('isi_id');
            $table->longText('isi_en');
            $table->string('kategori_berita', 60)->default('Siaran Pers')->index();
            $table->unsignedBigInteger('wilayah_id')->nullable()->index();
            $table->string('penulis', 100)->default('Humas Dinas Pariwisata Sulteng');
            $table->date('tanggal_terbit')->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengaduan', 40)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('destinasi_id')->nullable()->index();
            $table->string('nama_pelapor', 120);
            $table->string('email_pelapor', 150);
            $table->string('kategori', 60)->index();
            $table->string('judul', 160);
            $table->text('pesan');
            $table->string('status', 30)->default('baru')->index();
            $table->text('tanggapan_admin')->nullable();
            $table->unsignedBigInteger('ditanggapi_oleh')->nullable();
            $table->timestamp('ditanggapi_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('perubahan_destinasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destinasi_id')->index();
            $table->unsignedBigInteger('pengelola_id')->index();
            $table->string('jenis_perubahan', 60);
            $table->json('data_lama_json');
            $table->json('data_baru_json');
            $table->text('alasan_perubahan');
            $table->string('status', 30)->default('menunggu')->index();
            $table->text('catatan_admin')->nullable();
            $table->unsignedBigInteger('diproses_oleh')->nullable();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->foreign('destinasi_id')->references('id')->on('destinasi')->cascadeOnDelete();
        });

        Schema::create('etl_runs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_proses', 80)->index();
            $table->string('status', 30)->index();
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai')->nullable();
            $table->decimal('durasi_detik', 8, 2)->default(0);
            $table->unsignedInteger('jumlah_baris_diproses')->default(0);
            $table->json('metrik_json')->nullable();
            $table->text('ringkasan_log')->nullable();
            $table->string('dipicu_oleh', 80)->default('Sistem');
            $table->timestamps();
        });

        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_file');
            $table->string('jenis_data', 60);
            $table->unsignedInteger('total_baris')->default(0);
            $table->unsignedInteger('baris_berhasil')->default(0);
            $table->unsignedInteger('baris_gagal')->default(0);
            $table->json('detail_gagal_json')->nullable();
            $table->string('status', 30)->default('berhasil');
            $table->unsignedBigInteger('diimpor_oleh')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('nama_user', 120)->nullable();
            $table->string('peran', 40)->nullable();
            $table->string('aksi', 80)->index();
            $table->string('modul', 60)->index();
            $table->text('deskripsi');
            $table->json('metadata_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('import_logs');
        Schema::dropIfExists('etl_runs');
        Schema::dropIfExists('perubahan_destinasi');
        Schema::dropIfExists('pengaduan');
        Schema::dropIfExists('berita');
        Schema::dropIfExists('events');
        Schema::dropIfExists('itinerary_items');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('kamus_data');
        Schema::dropIfExists('ref_data_real');
        Schema::dropIfExists('funnel_bulanan');
        Schema::dropIfExists('tiket');
        Schema::dropIfExists('ulasan');
        Schema::dropIfExists('asal_pengunjung');
        Schema::dropIfExists('hotel_bulanan');
        Schema::dropIfExists('kunjungan_harian');
        Schema::dropIfExists('kunjungan_bulanan');
        Schema::dropIfExists('waktu_kalender');
        Schema::dropIfExists('destinasi');
        Schema::dropIfExists('wilayah');
    }
};

