<?php

namespace Tests\Feature;

use App\Models\Destinasi;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DestinationPhotosTest extends TestCase
{
    public function test_every_curated_destination_has_a_distinct_local_photo_and_source(): void
    {
        $destinations = Destinasi::orderBy('id')->get();
        $this->assertCount(27, $destinations);
        $hashes = [];

        foreach ($destinations as $destination) {
            $photo = $destination->foto_utama;
            $this->assertStringStartsWith('/images/destinasi/', $photo);
            $path = public_path(ltrim($photo, '/'));
            $this->assertFileExists($path);
            $size = getimagesize($path);
            $this->assertNotFalse($size);
            $this->assertGreaterThanOrEqual(500, $size[0]);
            $this->assertGreaterThanOrEqual(275, $size[1]);
            $hashes[] = hash_file('sha256', $path);
            $this->assertNotEmpty($destination->foto_attribution['credit']);
            $this->assertStringStartsWith('https://', $destination->foto_attribution['source']);
            $this->assertSame([$photo], $destination->galeri_json);
            $this->assertSame($photo, $destination->toArray()['foto_utama']);
        }

        $this->assertCount(27, array_unique($hashes));
    }

    public function test_catalog_map_and_chatbot_use_the_same_curated_photos(): void
    {
        foreach (['/destinasi', '/peta'] as $url) {
            $this->get($url)->assertOk()->assertInertia(function (AssertableInertia $page): void {
                $page->has('destinasiList', 27)
                    ->where('destinasiList.0.foto_utama', fn (string $photo): bool => str_starts_with($photo, '/images/destinasi/'))
                    ->has('destinasiList.0.foto_attribution.source');
            });
        }

        $destinations = $this->postJson('/chatbot/ask', ['message' => 'wisata bahari'])->assertOk()->json('destinations');
        $this->assertNotEmpty($destinations);
        foreach ($destinations as $destination) {
            $model = Destinasi::findOrFail($destination['id']);
            $this->assertSame($model->foto_utama, $destination['foto_utama']);
            $this->assertSame($model->foto_attribution, $destination['foto_attribution']);
        }
    }
}
