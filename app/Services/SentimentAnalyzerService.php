<?php

namespace App\Services;

class SentimentAnalyzerService
{
    /**
     * Multi-word positive slang, idioms, and double-negations checked BEFORE single words.
     * Prevents intensifiers like "gila" or "parah" in "gila keren banget" from being marked negative.
     *
     * @var array<string, float>
     */
    private const POSITIVE_PHRASES = [
        'gila keren' => 2.2,
        'gila bagus' => 2.2,
        'gila indah' => 2.2,
        'gila mantap' => 2.2,
        'gila cantik' => 2.2,
        'gila cakep' => 2.2,
        'gila banget' => 1.8,
        'keren gila' => 2.2,
        'bagus gila' => 2.2,
        'indah gila' => 2.2,
        'mantap gila' => 2.2,
        'cantik gila' => 2.2,
        'parah bagus' => 2.2,
        'parah keren' => 2.2,
        'parah indah' => 2.2,
        'parah cantik' => 2.2,
        'parah cakep' => 2.2,
        'parah enak' => 2.2,
        'parah mantap' => 2.2,
        'bagus parah' => 2.2,
        'keren parah' => 2.2,
        'indah parah' => 2.2,
        'cantik parah' => 2.2,
        'cakep parah' => 2.2,
        'enak parah' => 2.2,
        'seru parah' => 2.2,
        'mantap parah' => 2.2,
        'pecah banget' => 2.0,
        'pecah abis' => 2.0,
        'gak ada obat' => 2.2,
        'ga ada obat' => 2.2,
        'tidak ada obat' => 2.2,
        'bukan main' => 1.8,
        'luar biasa' => 2.0,
        'wajib dikunjungi' => 1.8,
        'recommended banget' => 2.0,
        'rekomended banget' => 2.0,
        'worth it' => 1.8,
        'best experience' => 2.0,
        'tidak mengecewakan' => 1.8,
        'tak mengecewakan' => 1.8,
        'gak mengecewakan' => 1.8,
        'ga mengecewakan' => 1.8,
        'nggak mengecewakan' => 1.8,
        'tidak nyesel' => 1.8,
        'gak nyesel' => 1.8,
        'ga nyesel' => 1.8,
        'nggak nyesel' => 1.8,
        'tidak menyesal' => 1.8,
        'gak menyesal' => 1.8,
        'tidak rugi' => 1.6,
        'gak rugi' => 1.6,
        'tidak kotor' => 1.4,
        'gak kotor' => 1.4,
        'tidak bau' => 1.4,
        'gak bau' => 1.4,
        'tidak mahal' => 1.4,
        'gak mahal' => 1.4,
        'tanpa sampah' => 1.6,
        'bebas sampah' => 1.6,
        'not bad' => 1.2,
        'no regrets' => 1.6,
    ];

    /**
     * Multi-word negative phrases checked BEFORE single words.
     *
     * @var array<string, float>
     */
    private const NEGATIVE_PHRASES = [
        'kecewa berat' => -2.4,
        'sangat kecewa' => -2.2,
        'mengecewakan sekali' => -2.2,
        'kotor banget' => -2.2,
        'kotor sekali' => -2.2,
        'kotor parah' => -2.4,
        'bau banget' => -2.2,
        'bau sekali' => -2.2,
        'bau parah' => -2.4,
        'rusak parah' => -2.4,
        'macet parah' => -2.2,
        'mahal banget' => -2.0,
        'mahal parah' => -2.2,
        'mahal sekali' => -2.0,
        'gila mahal' => -2.2,
        'harganya gila' => -2.0,
        'pungli parah' => -2.5,
        'tidak terawat' => -2.0,
        'kurang terawat' => -1.8,
        'tidak layak' => -2.2,
        'sampah berserakan' => -2.2,
        'banyak sampah' => -2.0,
        'pelayanan buruk' => -2.2,
        'tidak ramah' => -2.0,
        'kurang ramah' => -1.7,
        'tidak bersih' => -2.0,
        'kurang bersih' => -1.7,
        'tidak nyaman' => -2.0,
        'kurang nyaman' => -1.7,
        'tidak bagus' => -1.8,
        'kurang bagus' => -1.5,
        'tidak memuaskan' => -2.0,
        'kurang memuaskan' => -1.7,
        'buang waktu' => -2.2,
        'kapok ke sini' => -2.4,
        'jangan ke sini' => -2.4,
        'worst experience' => -2.4,
        'very dirty' => -2.2,
        'not worth' => -2.0,
    ];

    /**
     * Neutral / balanced idioms.
     *
     * @var list<string>
     */
    private const NEUTRAL_PHRASES = [
        'biasa saja',
        'biasa aja',
        'standar saja',
        'standar aja',
        'lumayan lah',
        'lumayan sih',
        'oke lah',
        'nothing special',
    ];

    /**
     * Negation words that invert the polarity of the following 1-2 tokens.
     *
     * @var list<string>
     */
    private const NEGATORS = [
        'tidak', 'tak', 'bukan', 'belum', 'kurang', 'jangan', 'tanpa',
        'gak', 'ga', 'nggak', 'ngga', 'tdk', 'blm', 'not', 'never', 'no', 'hardly',
    ];

    /**
     * Intensifiers (including Indonesian colloquial "gila", "parah", "edan") that boost
     * the adjacent sentiment word WITHOUT carrying negative polarity on their own.
     *
     * @var list<string>
     */
    private const INTENSIFIERS = [
        'sangat', 'banget', 'sekali', 'amat', 'super', 'bgt', 'bet', 'pol', 'poll',
        'pisan', 'really', 'very', 'extremely', 'gila', 'gilak', 'parah', 'edan',
    ];

    /**
     * Positive single-word lexicon.
     *
     * @var array<string, float>
     */
    private const POSITIVE_WORDS = [
        'bagus' => 1.2, 'keren' => 1.3, 'indah' => 1.3, 'cantik' => 1.3, 'cakep' => 1.2,
        'memukau' => 1.4, 'menakjubkan' => 1.4, 'eksotis' => 1.3, 'asri' => 1.2, 'sejuk' => 1.1,
        'bersih' => 1.2, 'rapi' => 1.1, 'nyaman' => 1.2, 'ramah' => 1.2, 'sopan' => 1.1,
        'membantu' => 1.0, 'puas' => 1.3, 'memuaskan' => 1.3, 'mantap' => 1.3, 'mantul' => 1.3,
        'recommended' => 1.3, 'rekomended' => 1.3, 'seru' => 1.2, 'menyenangkan' => 1.3,
        'enak' => 1.1, 'lezat' => 1.2, 'murah' => 1.0, 'terjangkau' => 1.1, 'jernih' => 1.2,
        'bening' => 1.2, 'tenang' => 1.0, 'damai' => 1.1, 'estetik' => 1.2, 'instagramable' => 1.1,
        'terawat' => 1.2, 'lengkap' => 1.0, 'aman' => 1.0, 'terbaik' => 1.4, 'juara' => 1.3,
        'suka' => 1.1, 'senang' => 1.2, 'betah' => 1.2, 'bagusnya' => 1.3, 'kerennya' => 1.3,
        'indahnya' => 1.3, 'cantiknya' => 1.3, 'amazing' => 1.4, 'beautiful' => 1.3, 'great' => 1.2,
        'clean' => 1.2, 'friendly' => 1.2, 'awesome' => 1.4, 'wonderful' => 1.4, 'stunning' => 1.4,
        'nice' => 1.0, 'love' => 1.3, 'good' => 1.1, 'excellent' => 1.4, 'best' => 1.3,
    ];

    /**
     * Negative single-word lexicon.
     *
     * @var array<string, float>
     */
    private const NEGATIVE_WORDS = [
        'kotor' => -1.3, 'jorok' => -1.4, 'sampah' => -1.2, 'bau' => -1.3, 'pesing' => -1.4,
        'busuk' => -1.4, 'rusak' => -1.3, 'hancur' => -1.4, 'jelek' => -1.3, 'buruk' => -1.4,
        'kecewa' => -1.4, 'mengecewakan' => -1.5, 'nyesel' => -1.4, 'menyesal' => -1.4,
        'mahal' => -1.1, 'kemahalan' => -1.3, 'pungli' => -1.5, 'preman' => -1.5, 'kasar' => -1.4,
        'jutek' => -1.3, 'sombong' => -1.3, 'cuek' => -1.1, 'lambat' => -1.1, 'macet' => -1.1,
        'becek' => -1.1, 'berlubang' => -1.2, 'gelap' => -1.0, 'berbahaya' => -1.4, 'kumuh' => -1.4,
        'terbengkalai' => -1.4, 'membosankan' => -1.2, 'bosen' => -1.1, 'keruh' => -1.2,
        'kapok' => -1.5, 'kotornya' => -1.3, 'baunya' => -1.3, 'mahalnya' => -1.2,
        'dirty' => -1.3, 'bad' => -1.3, 'terrible' => -1.5, 'horrible' => -1.5, 'awful' => -1.5,
        'rude' => -1.4, 'expensive' => -1.1, 'broken' => -1.3, 'disappointing' => -1.4,
        'disappointed' => -1.4, 'trash' => -1.3, 'smelly' => -1.3, 'poor' => -1.3, 'worst' => -1.5,
    ];

    /**
     * Analyze review text contextually and classify into positif, netral, or negatif.
     *
     * @return array{
     *     label: 'positif'|'netral'|'negatif',
     *     skor: float,
     *     net_score: float,
     *     kata_positif: list<string>,
     *     kata_negatif: list<string>
     * }
     */
    public function analyze(string $text): array
    {
        $normalized = $this->normalize($text);
        $working = ' '.$normalized.' ';

        $netScore = 0.0;
        $posMatches = [];
        $negMatches = [];
        $hasNeutralPhrase = false;

        // 1. Match multi-word positive idioms & slang first (longest phrases first)
        foreach (self::POSITIVE_PHRASES as $phrase => $weight) {
            if (str_contains($working, ' '.$phrase.' ')) {
                $netScore += $weight;
                $posMatches[] = $phrase;
                $working = str_replace(' '.$phrase.' ', ' ', $working);
            }
        }

        // 2. Match multi-word negative phrases
        foreach (self::NEGATIVE_PHRASES as $phrase => $weight) {
            if (str_contains($working, ' '.$phrase.' ')) {
                $netScore += $weight;
                $negMatches[] = $phrase;
                $working = str_replace(' '.$phrase.' ', ' ', $working);
            }
        }

        // 3. Check neutral idioms
        foreach (self::NEUTRAL_PHRASES as $phrase) {
            if (str_contains($working, ' '.$phrase.' ')) {
                $hasNeutralPhrase = true;
                $working = str_replace(' '.$phrase.' ', ' ', $working);
            }
        }

        // 4. Token-level scoring with negation window & intensifier boost
        $tokens = array_values(array_filter(explode(' ', trim($working)), static fn (string $t): bool => $t !== ''));
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $this->collapseRepeatedChars($tokens[$i]);
            $prev1 = $i >= 1 ? $this->collapseRepeatedChars($tokens[$i - 1]) : '';
            $prev2 = $i >= 2 ? $this->collapseRepeatedChars($tokens[$i - 2]) : '';
            $next1 = $i + 1 < $count ? $this->collapseRepeatedChars($tokens[$i + 1]) : '';

            $isNegated = in_array($prev1, self::NEGATORS, true) || in_array($prev2, self::NEGATORS, true);
            $isIntensified = in_array($prev1, self::INTENSIFIERS, true) || in_array($next1, self::INTENSIFIERS, true);
            $multiplier = $isIntensified ? 1.35 : 1.0;

            if (isset(self::POSITIVE_WORDS[$token])) {
                $val = self::POSITIVE_WORDS[$token] * $multiplier;
                if ($isNegated) {
                    $netScore -= ($val * 1.1);
                    $negMatches[] = trim($prev1.' '.$token);
                } else {
                    $netScore += $val;
                    $posMatches[] = $token;
                }
            } elseif (isset(self::NEGATIVE_WORDS[$token])) {
                $val = self::NEGATIVE_WORDS[$token] * $multiplier;
                if ($isNegated) {
                    $netScore += (abs($val) * 0.85);
                    $posMatches[] = trim($prev1.' '.$token);
                } else {
                    $netScore += $val;
                    $negMatches[] = $token;
                }
            }
        }

        // 5. Final classification: strictly text-driven
        if ($netScore >= 0.65) {
            $label = 'positif';
        } elseif ($netScore <= -0.65) {
            $label = 'negatif';
        } else {
            $label = 'netral';
        }

        // If neutral phrase is present and sentiment signal is weak, keep netral
        if ($hasNeutralPhrase && abs($netScore) < 1.3) {
            $label = 'netral';
        }

        // Convert netScore into 0.05..0.99 confidence/positivity score (0.50 = netral)
        $sigmoid = 1.0 / (1.0 + exp(-0.85 * $netScore));
        $skor = round(min(0.99, max(0.05, $sigmoid)), 4);

        return [
            'label' => $label,
            'skor' => $skor,
            'net_score' => round($netScore, 3),
            'kata_positif' => array_values(array_unique($posMatches)),
            'kata_negatif' => array_values(array_unique($negMatches)),
        ];
    }

    private function normalize(string $text): string
    {
        $lower = mb_strtolower($text, 'UTF-8');
        $clean = preg_replace('/[^a-z0-9\s]+/u', ' ', $lower) ?? $lower;

        return trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);
    }

    private function collapseRepeatedChars(string $word): string
    {
        // Normalize expressive elongation like "kerennnn" -> "keren", "bagusss" -> "bagus"
        return preg_replace('/(.)\1{2,}/u', '$1', $word) ?? $word;
    }
}
