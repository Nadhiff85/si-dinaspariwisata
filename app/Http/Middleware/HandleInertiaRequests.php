<?php

namespace App\Http\Middleware;

use App\Models\Destinasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'peran' => $user->peran,
                    'destinasi_id' => $user->destinasi_id,
                    'bahasa' => $user->bahasa,
                    'tema' => $user->tema,
                    'telepon' => $user->telepon,
                    'asal_wilayah' => $user->asal_wilayah,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'locale' => fn () => $request->session()->get('locale', $user->bahasa ?? 'id'),
            'quickDestinations' => fn () => Schema::hasTable('destinasi')
                ? Destinasi::query()
                    ->with('wilayah:id,nama_wilayah')
                    ->orderBy('id')
                    ->get(['id', 'wilayah_id', 'nama_destinasi', 'slug', 'kategori', 'harga_tiket_dewasa', 'jam_operasional'])
                : [],
        ];
    }
}

