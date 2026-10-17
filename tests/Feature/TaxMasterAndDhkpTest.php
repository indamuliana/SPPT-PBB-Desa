<?php

namespace Tests\Feature;

use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use App\Services\DhkpImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TaxMasterAndDhkpTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kadus;
    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Desa Sukahaji',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
        ]);

        $this->admin = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kadus = User::factory()->create([
            'username' => 'kadus1',
            'role' => 'kadus',
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_view_taxpayers_and_objects(): void
    {
        $wp = Taxpayer::create(['name' => 'Budi Santoso', 'dusun' => 'Dusun I']);
        TaxObject::create([
            'nop' => '001205000100100010',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->kadus)->get('/taxpayers');
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');

        $responseOp = $this->actingAs($this->kadus)->get('/tax-objects');
        $responseOp->assertStatus(200);
        $responseOp->assertSee('00.12.050.001.001-0001.0');
    }

    public function test_admin_can_create_taxpayer(): void
    {
        $response = $this->actingAs($this->admin)->post('/taxpayers', [
            'name' => 'Dedi Iskandar',
            'nik' => '3212051234560001',
            'phone' => '081234567890',
            'address' => 'Jl. Blok Tengah',
            'dusun' => 'Dusun II Sukajaya',
        ]);

        $response->assertRedirect('/taxpayers');
        $this->assertDatabaseHas('taxpayers', [
            'name' => 'Dedi Iskandar',
            'nik' => '3212051234560001',
        ]);
    }

    public function test_nop_preserves_leading_zero(): void
    {
        $wp = Taxpayer::create(['name' => 'Siti Rohimah']);

        $response = $this->actingAs($this->admin)->post('/tax-objects', [
            'nop' => '001205000100200050',
            'taxpayer_id' => $wp->id,
            'address' => 'Blok Sawah Tengah',
            'dusun' => 'Dusun I Babakan',
            'status' => 'normal',
        ]);

        $response->assertRedirect('/tax-objects');
        $this->assertDatabaseHas('tax_objects', [
            'nop' => '001205000100200050',
        ]);

        $saved = TaxObject::where('nop', '001205000100200050')->first();
        $this->assertNotNull($saved);
        $this->assertEquals('001205000100200050', $saved->nop);
        $this->assertStringStartsWith('00.', $saved->formatted_nop);
    }

    public function test_admin_can_create_sppt_manually(): void
    {
        $wp = Taxpayer::create(['name' => 'Cecep Kurnia']);
        $op = TaxObject::create([
            'nop' => '321205000100300099',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)->post('/sppts', [
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'pbb_terutang' => 175000,
            'due_date' => '2026-09-30',
        ]);

        $response->assertRedirect('/sppts?year=2026');
        $this->assertDatabaseHas('sppts', [
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'total_bill' => 175000,
            'status' => 'unpaid',
        ]);
    }

    public function test_non_admin_cannot_create_sppt(): void
    {
        $wp = Taxpayer::create(['name' => 'Warga']);
        $op = TaxObject::create([
            'nop' => '321205000100400011',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->kadus)->post('/sppts', [
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'pbb_terutang' => 50000,
        ]);

        $response->assertStatus(403);
    }

    public function test_dhkp_import_service_processes_csv_file(): void
    {
        $csvContent = "nop,nama_wp,alamat_op,dusun,rt,rw,luas_bumi,luas_bangunan,pbb_terutang,tahun\n"
            . "321205000100500010,Bpk. H. Sukardi,Blok Pesawahan,Dusun I Babakan,01,01,500,120,250000,2026\n"
            . "321205000100500020,Ibu Maryamah,Blok Karang Tengah,Dusun II Sukajaya,02,02,300,80,140000,2026\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'dhkp_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $service = new DhkpImportService();
        $result = $service->import($tempFile, 2026);

        unlink($tempFile);

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);

        $this->assertDatabaseHas('taxpayers', ['name' => 'Bpk. H. Sukardi']);
        $this->assertDatabaseHas('tax_objects', ['nop' => '321205000100500010']);
        $this->assertDatabaseHas('sppts', [
            'tax_year' => 2026,
            'pbb_terutang' => 250000,
            'total_bill' => 250000,
        ]);
    }

    public function test_sppt_status_recalculation(): void
    {
        $wp = Taxpayer::create(['name' => 'Ujang']);
        $op = TaxObject::create([
            'nop' => '321205000100600010',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $sppt = Sppt::create([
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->assertTrue($sppt->isUnpaid());
        $this->assertEquals(100000, $sppt->remaining_bill);

        // Cicilan
        $sppt->paid_amount = 40000;
        $sppt->recalculateStatus();
        $this->assertTrue($sppt->isPartial());
        $this->assertEquals(60000, $sppt->remaining_bill);

        // Lunas
        $sppt->paid_amount = 100000;
        $sppt->recalculateStatus();
        $this->assertTrue($sppt->isPaid());
        $this->assertEquals(0, $sppt->remaining_bill);
    }

    public function test_admin_can_download_dhkp_template_xlsx(): void
    {
        $response = $this->actingAs($this->admin)->get('/sppts/import/template?format=xlsx&tax_year=2026');
        $response->assertStatus(200);
        $this->assertStringContainsString('Template_DHKP_SPPT', $response->headers->get('content-disposition', ''));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition', ''));
    }

    public function test_admin_can_download_dhkp_template_csv(): void
    {
        $response = $this->actingAs($this->admin)->get('/sppts/import/template?format=csv&tax_year=2026');
        $response->assertStatus(200);
        $this->assertStringContainsString('Template_DHKP_SPPT', $response->headers->get('content-disposition', ''));
        $this->assertStringContainsString('.csv', $response->headers->get('content-disposition', ''));
    }

    public function test_generated_template_can_be_imported_cleanly_with_components_and_nik(): void
    {
        \App\Models\Dusun::create(['name' => 'Dusun I Babakan']);
        \App\Models\Dusun::create(['name' => 'Dusun II Sukajaya']);
        \App\Models\Dusun::create(['name' => 'Dusun III Mekarsari']);

        $service = new DhkpImportService();
        $tempCsv = $service->generateTemplate('csv', 2026);

        $result = $service->import($tempCsv, 2026);
        @unlink($tempCsv);

        $this->assertEquals(3, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);

        // Check NIK and Taxpayer
        $wp1 = Taxpayer::where('name', 'H. AHMAD SUBAGJA')->first();
        $this->assertNotNull($wp1);
        $this->assertEquals('3211140105820001', $wp1->nik);

        // Check TaxObject and Dusun mapping
        $to1 = TaxObject::where('nop', '321114000100100010')->first();
        $this->assertNotNull($to1);
        $this->assertNotNull($to1->dusun_id);

        // Check 4 Components for row 3 (which has bumi_bersama and bangunan_bersama)
        $to3 = TaxObject::where('nop', '321114000100300030')->first();
        $this->assertNotNull($to3);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $to3->id,
            'type' => 'bumi_bersama',
            'area_m2' => 25.00,
        ]);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $to3->id,
            'type' => 'bangunan_bersama',
            'area_m2' => 15.00,
        ]);
    }

    public function test_dhkp_import_preserves_financial_invariant_for_paid_sppt(): void
    {
        $wp = Taxpayer::create(['name' => 'Warga Taat']);
        $op = TaxObject::create([
            'nop' => '321205000100999999',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $sppt = Sppt::create([
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'pbb_terutang' => 150000,
            'total_bill' => 150000,
            'paid_amount' => 100000, // already partially paid in cashier
            'status' => 'partial',
        ]);

        // Attempt import with a lower bill (50000) than what was already paid (100000)
        $csvContent = "nop,nama_wp,pbb_terutang,tahun\n"
            . "321205000100999999,Warga Taat,50000,2026\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'dhkp_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $service = new DhkpImportService();
        $result = $service->import($tempFile, 2026);
        @unlink($tempFile);

        $this->assertEquals(1, $result['imported']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('melindungi saldo kasir', $result['errors'][0]);

        $sppt->refresh();
        // Total bill must NOT be lowered below 100000
        $this->assertEquals(150000, (float)$sppt->total_bill);
        $this->assertEquals(100000, (float)$sppt->paid_amount);
        $this->assertEquals('partial', $sppt->status);
    }
}
