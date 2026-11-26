<?php

namespace Tests\Feature;

use App\Services\SentimentAnalyzerService;
use Tests\TestCase;

class SentimentAnalysisTest extends TestCase
{
    public function test_colloquial_positive_slang_like_gila_and_parah_is_classified_positive(): void
    {
        $analyzer = new SentimentAnalyzerService;

        $res1 = $analyzer->analyze('Gila keren banget pemandangannya, gak nyesel jauh-jauh ke sini!');
        $this->assertSame('positif', $res1['label']);
        $this->assertGreaterThan(0.65, $res1['skor']);

        $res2 = $analyzer->analyze('Parah bagusnya pantai ini, tidak mengecewakan sama sekali.');
        $this->assertSame('positif', $res2['label']);

        $res3 = $analyzer->analyze('Pecah banget sunset di Bukit Salena, gak ada obat!');
        $this->assertSame('positif', $res3['label']);
    }

    public function test_ambiguous_word_gila_alone_is_not_classified_as_negative(): void
    {
        $analyzer = new SentimentAnalyzerService;

        $res = $analyzer->analyze('Gila, tempat ini unik sekali.');
        $this->assertNotSame('negatif', $res['label']);
    }

    public function test_negated_positive_and_real_complaints_are_classified_negative(): void
    {
        $analyzer = new SentimentAnalyzerService;

        $res1 = $analyzer->analyze('Tempatnya tidak bersih dan petugas kurang ramah, kecewa berat.');
        $this->assertSame('negatif', $res1['label']);
        $this->assertLessThan(0.4, $res1['skor']);

        $res2 = $analyzer->analyze('Harganya gila mahal, toilet kotor parah dan bau.');
        $this->assertSame('negatif', $res2['label']);
    }

    public function test_factual_and_balanced_reviews_are_classified_neutral(): void
    {
        $analyzer = new SentimentAnalyzerService;

        $res1 = $analyzer->analyze('Tiket masuk 10 ribu, buka dari jam 8 pagi, tempatnya biasa saja.');
        $this->assertSame('netral', $res1['label']);

        $res2 = $analyzer->analyze('Pemandangan bagus tapi toilet agak kotor.');
        $this->assertSame('netral', $res2['label']);
    }
}
