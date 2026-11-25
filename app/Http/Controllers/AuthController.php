<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        $demoAccounts = User::query()
            ->whereIn('id', [1, 2, 3, 4, 5])
            ->with('destinasi:id,nama_destinasi')
            ->get(['id', 'name', 'email', 'peran', 'destinasi_id', 'bahasa']);

        $allPengelolaAccounts = User::query()
            ->where('peran', 'pengelola_destinasi')
            ->with('destinasi.wilayah:id,nama_wilayah')
            ->orderBy('destinasi_id')
            ->get(['id', 'name', 'email', 'peran', 'destinasi_id', 'bahasa']);

        return Inertia::render('Auth/Login', [
            'demoAccounts' => $demoAccounts,
            'allPengelolaAccounts' => $allPengelolaAccounts,
            'redirectUrl' => (string) $request->query('redirect', ''),
            'reason' => (string) $request->query('reason', ''),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 6)) {
            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan tunggu 60 detik.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Akun ini sedang dinonaktifkan oleh Admin Dinas.']);
            }

            $request->session()->put('locale', $user->bahasa ?? 'id');

            ActivityLog::create([
                'user_id' => $user->id,
                'nama_user' => $user->name,
                'peran' => $user->peran,
                'aksi' => 'Login Sistem',
                'modul' => 'Autentikasi',
                'deskripsi' => "Pengguna {$user->name} ({$user->peran}) berhasil masuk.",
                'ip_address' => $request->ip(),
            ]);

            $redirectTarget = (string) $request->input('redirect', '');
            $isSafeInternalRedirect = str_starts_with($redirectTarget, '/') && ! str_starts_with($redirectTarget, '//');

            if ($user->peran === 'admin') {
                if ($isSafeInternalRedirect) {
                    return redirect($redirectTarget)->with('success', "Selamat datang kembali, {$user->name}!");
                }

                return redirect()->route('admin.dashboard')->with('success', "Selamat datang kembali, {$user->name}!");
            }
            if ($user->peran === 'pengelola_destinasi') {
                if ($isSafeInternalRedirect) {
                    return redirect($redirectTarget)->with('success', "Selamat datang, {$user->name}!");
                }

                return redirect()->route('pengelola.dashboard')->with('success', "Selamat datang di Panel Pengelola, {$user->name}!");
            }

            if ($isSafeInternalRedirect) {
                return redirect($redirectTarget)->with('success', "Selamat datang di Jelajah Sulteng, {$user->name}!");
            }

            return redirect()->intended(route('beranda'))->with('success', "Selamat datang di Jelajah Sulteng, {$user->name}!");
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'bahasa' => ['nullable', 'in:id,en'],
            'asal_wilayah' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'peran' => 'user',
            'bahasa' => $validated['bahasa'] ?? 'id',
            'tema' => 'terang',
            'kode_pengguna' => 'U'.str_pad((string) rand(300, 999), 3, '0', STR_PAD_LEFT),
            'asal_wilayah' => $validated['asal_wilayah'] ?? 'Sulawesi Tengah',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('locale', $user->bahasa);

        return redirect()->route('beranda')->with('success', 'Akun wisatawan berhasil dibuat! Selamat menjelajahi Sulawesi Tengah.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('info', 'Anda telah keluar dari sesi.');
    }

    public function switchLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:id,en'],
        ]);

        $request->session()->put('locale', $validated['locale']);
        if ($request->user()) {
            $request->user()->update(['bahasa' => $validated['locale']]);
        }

        return back();
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'asal_wilayah' => ['nullable', 'string', 'max:100'],
            'bahasa' => ['required', 'in:id,en'],
            'tema' => ['required', 'in:terang,gelap,sistem'],
        ]);

        $user->update($validated);
        $request->session()->put('locale', $validated['bahasa']);

        return back()->with('success', 'Profil dan preferensi akun berhasil diperbarui.');
    }
}

