<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengelolaDashboardController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\TouristActionController;
use Illuminate\Support\Facades\Route;

// Public Tourist & Visitor Routes (FR-01, FR-02, FR-03, FR-04, FR-11, FR-12, FR-13, FR-14, FR-15, FR-16, FR-17, FR-18, FR-19)
Route::get('/', [PublicPortalController::class, 'beranda'])->name('beranda');
Route::get('/destinasi', [PublicPortalController::class, 'jelajah'])->name('destinasi.index');
Route::get('/destinasi/{slug}', [PublicPortalController::class, 'detailDestinasi'])->name('destinasi.show');
Route::get('/peta', [PublicPortalController::class, 'petaWisata'])->name('peta');
Route::get('/statistik', [PublicPortalController::class, 'statistikPublik'])->name('statistik');
Route::get('/informasi', [PublicPortalController::class, 'informasiPublik'])->name('informasi');
Route::post('/pengaduan', [PublicPortalController::class, 'storePengaduan'])->name('pengaduan.store');
Route::post('/chatbot/ask', [PublicPortalController::class, 'chatbotAsk'])->name('chatbot.ask');

// Language Switcher
Route::post('/locale', [AuthController::class, 'switchLocale'])->name('locale.switch');

// Authentication & Quick Account Switcher Routes (FR-05 — accessible anytime so users can switch between Main Admin & 27 Destination Accounts)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated User / Tourist Routes (FR-06, FR-07, FR-08, FR-09, FR-10)
Route::middleware('auth')->group(function () {
    Route::post('/profil', [AuthController::class, 'updateProfile'])->name('profil.update');

    // Wishlist (FR-06)
    Route::get('/wishlist', [TouristActionController::class, 'wishlistIndex'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [TouristActionController::class, 'toggleWishlist'])->name('wishlist.toggle');

    // Itinerary Planner & PDF Export (FR-07)
    Route::get('/itinerari', [TouristActionController::class, 'itinerariIndex'])->name('itinerari.index');
    Route::post('/itinerari', [TouristActionController::class, 'storeItinerari'])->name('itinerari.store');
    Route::delete('/itinerari/{id}', [TouristActionController::class, 'deleteItinerari'])->name('itinerari.delete');
    Route::get('/itinerari/{id}/pdf', [TouristActionController::class, 'exportItinerariPdf'])->name('itinerari.pdf');
    Route::post('/itinerari/{id}/pesan-semua', [TouristActionController::class, 'pesanSemuaTiketItinerari'])->name('itinerari.pesan_semua');

    // Simulated E-Ticket Booking & History (FR-09, FR-10)
    Route::get('/tiket', [TouristActionController::class, 'riwayatTiket'])->name('tiket.riwayat');
    Route::post('/tiket/pesan', [TouristActionController::class, 'pesanTiket'])->name('tiket.pesan');
    Route::post('/tiket/{id}/batal', [TouristActionController::class, 'batalkanTiket'])->name('tiket.batal');

    // 5-Aspect Rating & Review (FR-08)
    Route::post('/ulasan', [TouristActionController::class, 'storeUlasan'])->name('ulasan.store');
});

// Destination Manager Routes (Pengelola Destinasi & Admin — FR-20..FR-25)
Route::middleware(['auth', 'role:pengelola_destinasi,admin'])->prefix('pengelola')->name('pengelola.')->group(function () {
    Route::get('/dashboard', [PengelolaDashboardController::class, 'index'])->name('dashboard');
    Route::post('/kunjungan-harian', [PengelolaDashboardController::class, 'storeKunjunganHarian'])->name('kunjungan.store');
    Route::post('/kunjungan-csv', [PengelolaDashboardController::class, 'importKunjunganCsv'])->name('kunjungan.csv');
    Route::post('/verifikasi-tiket', [PengelolaDashboardController::class, 'verifikasiTiket'])->name('tiket.verifikasi');
    Route::post('/ulasan/{id}/balas', [PengelolaDashboardController::class, 'balasUlasan'])->name('ulasan.balas');
    Route::post('/perubahan-profil', [PengelolaDashboardController::class, 'ajukanPerubahanProfil'])->name('profil.ajukan');
    Route::post('/event', [PengelolaDashboardController::class, 'ajukanEvent'])->name('event.ajukan');
    Route::post('/ajukan-event', [PengelolaDashboardController::class, 'ajukanEvent'])->name('event.ajukan.alias');
});

// Executive Admin Dinas Routes (FR-30..FR-41)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/etl/run', [AdminDashboardController::class, 'triggerEtl'])->name('etl.run');
    Route::post('/models/run', [AdminDashboardController::class, 'triggerModels'])->name('models.run');
    Route::post('/rekomendasi/{id}/status', [AdminDashboardController::class, 'updateRekomendasiStatus'])->name('rekomendasi.status');
    Route::post('/perubahan/{id}/proses', [AdminDashboardController::class, 'prosesPerubahanDestinasi'])->name('perubahan.proses');
    Route::post('/perubahan-destinasi/{id}/proses', [AdminDashboardController::class, 'prosesPerubahanDestinasi'])->name('perubahan_destinasi.proses');
    Route::post('/destinasi/save', [AdminDashboardController::class, 'saveDestinasi'])->name('destinasi.save');
    Route::post('/destinasi/simpan', [AdminDashboardController::class, 'saveDestinasi'])->name('destinasi.simpan');
    Route::post('/users/save', [AdminDashboardController::class, 'saveUser'])->name('users.save');
    Route::post('/pengguna/simpan', [AdminDashboardController::class, 'saveUser'])->name('pengguna.simpan');
    Route::post('/ulasan/{id}/moderasi', [AdminDashboardController::class, 'moderasiUlasan'])->name('ulasan.moderasi');
    Route::post('/pengaduan/{id}/tanggapi', [AdminDashboardController::class, 'tanggapiPengaduan'])->name('pengaduan.tanggapi');
    Route::post('/event/{id}/proses', [AdminDashboardController::class, 'prosesEvent'])->name('event.proses');
    Route::post('/berita/store', [AdminDashboardController::class, 'storeBerita'])->name('berita.store');
    Route::post('/berita/simpan', [AdminDashboardController::class, 'storeBerita'])->name('berita.simpan');
    Route::get('/export/pdf', [AdminDashboardController::class, 'exportLaporanPdf'])->name('export.pdf');
    Route::get('/ekspor/pdf', [AdminDashboardController::class, 'exportLaporanPdf'])->name('ekspor.pdf');
    Route::get('/export/csv', [AdminDashboardController::class, 'exportDataCsv'])->name('export.csv');
    Route::get('/ekspor/csv', [AdminDashboardController::class, 'exportDataCsv'])->name('ekspor.csv');
});
