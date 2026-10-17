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
}
