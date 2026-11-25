<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WarehouseEtlService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecommendationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'warehouse'];

    private function createRecommendation(): int
    {
        return DB::connection('warehouse')->table('rekomendasi_aksi')->insertGetId([
            'kode_aturan' => 'TEST-REVIEW',
            'judul_id' => 'Periksa ketersediaan petugas',
            'judul_en' => 'Check staffing availability',
            'kondisi_terdeteksi_id' => 'Puncak kunjungan tercatat.',
            'kondisi_terdeteksi_en' => 'A visitation peak was recorded.',
            'saran_tindakan_id' => 'Periksa jadwal petugas.',
            'saran_tindakan_en' => 'Review staff schedules.',
            'data_pendukung' => 'Rekap kunjungan bulanan.',
        ]);
    }

    public function test_legacy_rule_descriptions_do_not_overstate_their_evidence(): void
    {
        $cases = [
            ['RULE-01', 'Prediksi turun lebih dari 15%.', 'Forecast falls over 15%.', 'Pertumbuhan tahunan terendah (-13.63%)', '-16.4% Proyeksi Off-Peak', '15%'],
            ['RULE-02', 'Proporsi keluhan ulasan pada aspek kebersihan mencapai 39.13%.', 'Review complaints on cleanliness & facilities reached 39.13%.', 'Keluhan Kebersihan: 30.43% | Sentimen Negatif: 39.13%', '39.13% Keluhan', 'kebersihan mencapai 39.13%'],
            ['RULE-03', 'Keramaian 102.7%, mendekati batas daya dukung harian.', 'Crowd level 102.7%, approaching daily carrying capacity.', 'Rata-rata: 102.7%', '102.7%', 'mendekati'],
            ['RULE-04', 'Destinasi dengan rating sangat tinggi (3.64/5).', 'Destination with high rating (3.64/5).', 'Rating: 3.64', '3.64', 'sangat tinggi'],
            ['RULE-05', 'TPK di bawah ambang ideal 35%.', 'TPK below the 35% threshold.', 'TPK: 33.5%', '33.5%', 'ambang ideal'],
        ];
        foreach ($cases as [$rule, $conditionId, $conditionEn, $evidence, $metric, $misleadingText]) {
            $record = (object) [
                'kode_aturan' => $rule,
                'kondisi_terdeteksi_id' => $conditionId,
                'kondisi_terdeteksi_en' => $conditionEn,
                'data_pendukung' => $evidence,
                'metrik_nilai' => $metric,
                'status' => 'diproses',
                'catatan_tindak_lanjut' => 'Catatan petugas yang harus tetap tersimpan.',
            ];
            $clarified = app(WarehouseEtlService::class)->clarifyRecommendationEvidence($record);
            $this->assertStringNotContainsString($misleadingText, $clarified->kondisi_terdeteksi_id);
            $this->assertSame($evidence, $clarified->data_pendukung);
            $this->assertSame($record->status, $clarified->status);
            $this->assertSame($record->catatan_tindak_lanjut, $clarified->catatan_tindak_lanjut);
            $this->assertSame($conditionId, $record->kondisi_terdeteksi_id);
        }
    }

    public function test_recommendations_include_bilingual_titles_evidence_and_actions(): void
    {
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();
        $this->createRecommendation();
        $page = $this->actingAs($admin)->get('/admin/dashboard?tab=rekomendasi')->assertOk()->viewData('page');
        $recommendations = $page['props']['rekomendasiList'];
        $this->assertNotEmpty($recommendations);
        foreach ($recommendations as $recommendation) {
            foreach (['judul_id', 'judul_en', 'kondisi_terdeteksi_id', 'kondisi_terdeteksi_en', 'saran_tindakan_id', 'saran_tindakan_en', 'data_pendukung'] as $field) {
                $this->assertNotEmpty($recommendation->{$field}, $field.' must be available for recommendation '.$recommendation->id);
            }
        }
    }

    public function test_status_updates_preserve_existing_notes_when_no_note_is_submitted(): void
    {
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();
        $warehouse = DB::connection('warehouse');
        $recommendation = $warehouse->table('rekomendasi_aksi')->where('id', $this->createRecommendation())->first();
        $warehouse->table('rekomendasi_aksi')->where('id', $recommendation->id)->update(['catatan_tindak_lanjut' => 'Catatan yang harus dipertahankan.']);
        $this->actingAs($admin)->post('/admin/rekomendasi/'.$recommendation->id.'/status', ['status' => 'diproses'])->assertSessionHasNoErrors()->assertRedirect();
        $updated = $warehouse->table('rekomendasi_aksi')->where('id', $recommendation->id)->first();
        $this->assertSame('diproses', $updated->status);
        $this->assertSame('Catatan yang harus dipertahankan.', $updated->catatan_tindak_lanjut);
        $this->assertSame($admin->id, (int) $updated->ditindaklanjuti_oleh);
        $this->assertNotNull($updated->ditindaklanjuti_pada);
    }

    public function test_explicit_notes_can_be_saved_and_cleared_but_missing_records_are_rejected(): void
    {
        $admin = User::where('email', 'admin@pariwisata-sulteng.test')->firstOrFail();
        $warehouse = DB::connection('warehouse');
        $id = $this->createRecommendation();
        $this->actingAs($admin)->post('/admin/rekomendasi/'.$id.'/status', ['status' => 'selesai', 'catatan_tindak_lanjut' => 'Inspeksi fasilitas telah dilakukan.'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('rekomendasi_aksi', ['id' => $id, 'status' => 'selesai', 'catatan_tindak_lanjut' => 'Inspeksi fasilitas telah dilakukan.'], 'warehouse');
        $this->post('/admin/rekomendasi/'.$id.'/status', ['status' => 'baru', 'catatan_tindak_lanjut' => ''])->assertSessionHasNoErrors();
        $this->assertNull($warehouse->table('rekomendasi_aksi')->where('id', $id)->value('catatan_tindak_lanjut'));
        $missingId = (int) $warehouse->table('rekomendasi_aksi')->max('id') + 100;
        $this->post('/admin/rekomendasi/'.$missingId.'/status', ['status' => 'diproses'])->assertNotFound();
    }
}
