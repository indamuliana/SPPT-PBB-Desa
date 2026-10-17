<?php

namespace Tests\Feature;

use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedSpptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Village::current()->update([
            'tax_rate_percent' => 0.100,
            'active_tax_year' => 2026,
        ]);
    }

    public function test_admin_can_access_unified_sppt_create_page(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test', 'role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('sppts.create-unified'));
        $response->assertStatus(200);
        $response->assertSee('Perekaman SPPT Terpadu');
        $response->assertSee('BUMI BERSAMA');
        $response->assertSee('BANG BERSAMA');
    }

    public function test_admin_can_create_unified_sppt_with_new_taxpayer_and_4_components(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test', 'role' => 'admin', 'is_active' => true]);

        // Data matches sppt.html
        // Bumi: 45 x 1.200.000 = 54.000.000
        // Bangunan: 36 x 2.500.000 = 90.000.000
        // Bumi Bersama: 12 x 1.200.000 = 14.400.000
        // Bang Bersama: 18 x 2.500.000 = 45.000.000
        // Total NJOP = 203.400.000
        // NJOPTKP = 10.000.000
        // NJOP Perhitungan = 193.400.000
        // Tarif = 0.1% -> PBB Terutang = 193.400
        $payload = [
            'taxpayer_mode' => 'new',
            'new_taxpayer_name' => 'INDA MULIANA',
            'new_taxpayer_nik' => '3205012345678901',
            'new_taxpayer_address' => 'JL. RAYA UTAMA NO. 45',
            'new_taxpayer_rt' => '003',
            'new_taxpayer_rw' => '005',
            'new_taxpayer_phone' => '08123456789',

            'nop' => '32.05.010.001.002-0034.0',
            'object_address' => 'JL. RAYA UTAMA NO. 45 APARTEMEN LT. 05 NO. 12',
            'object_rt' => '003',
            'object_rw' => '005',
            'object_dusun' => 'Sukamaju',

            'bumi_area_m2' => 45,
            'bumi_class_code' => 'A35',
            'bumi_njop_per_m2' => 1200000,

            'bangunan_area_m2' => 36,
            'bangunan_class_code' => 'B20',
            'bangunan_njop_per_m2' => 2500000,

            'bumi_bersama_area_m2' => 12,
            'bumi_class_code' => 'A35',
            'bumi_bersama_njop_per_m2' => 1200000,

            'bangunan_bersama_area_m2' => 18,
            'bangunan_class_code' => 'B20',
            'bangunan_bersama_njop_per_m2' => 2500000,

            'tax_year' => 2026,
            'njoptkp' => 10000000,
        ];

        $response = $this->actingAs($admin)->post(route('sppts.store-unified'), $payload);

        $response->assertRedirect(route('sppts.index', ['year' => 2026]));
        $response->assertSessionHas('success');

        // Check Taxpayer
        $this->assertDatabaseHas('taxpayers', [
            'name' => 'INDA MULIANA',
            'nik' => '3205012345678901',
        ]);

        // Check TaxObject
        $this->assertDatabaseHas('tax_objects', [
            'nop' => '320501000100200340',
            'dusun' => 'Sukamaju',
        ]);

        $taxObject = TaxObject::where('nop', '320501000100200340')->first();

        // Check 4 Components
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $taxObject->id,
            'type' => 'bumi',
            'area_m2' => 45,
            'total_njop' => 54000000,
        ]);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $taxObject->id,
            'type' => 'bangunan',
            'area_m2' => 36,
            'total_njop' => 90000000,
        ]);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $taxObject->id,
            'type' => 'bumi_bersama',
            'area_m2' => 12,
            'total_njop' => 14400000,
        ]);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $taxObject->id,
            'type' => 'bangunan_bersama',
            'area_m2' => 18,
            'total_njop' => 45000000,
        ]);

        // Check SPPT & Calculations
        $this->assertDatabaseHas('sppts', [
            'tax_object_id' => $taxObject->id,
            'tax_year' => 2026,
            'njop_dasar' => 203400000,
            'njoptkp' => 10000000,
            'njop_perhitungan' => 193400000,
            'pbb_terutang' => 193400,
            'total_bill' => 193400,
            'status' => 'unpaid',
        ]);
    }

    public function test_store_unified_preserves_paid_amount_and_recalculates_status(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test2', 'role' => 'admin', 'is_active' => true]);

        $wp = Taxpayer::create(['name' => 'Warga Taat', 'nik' => '3205019999990001']);
        $obj = TaxObject::create([
            'nop' => '320501000100999001',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        $sppt = \App\Models\Sppt::create([
            'tax_object_id' => $obj->id,
            'tax_year' => 2026,
            'njop_dasar' => 100000000,
            'njoptkp' => 10000000,
            'njop_perhitungan' => 90000000,
            'tarif_percent' => 0.100,
            'pbb_terutang' => 90000,
            'fine_amount' => 5000,
            'total_bill' => 95000,
            'paid_amount' => 50000,
            'status' => 'partial',
        ]);

        // Re-record via store-unified with higher components leading to pbb_terutang = 100000
        // (110.000.000 - 10.000.000 = 100.000.000 * 0.1% = 100.000)
        $payload = [
            'taxpayer_mode' => 'existing',
            'taxpayer_id' => $wp->id,
            'nop' => $obj->nop,
            'bumi_area_m2' => 110,
            'bumi_njop_per_m2' => 1000000,
            'tax_year' => 2026,
            'njoptkp' => 10000000,
        ];

        $response = $this->actingAs($admin)->post(route('sppts.store-unified'), $payload);
        $response->assertRedirect(route('sppts.index', ['year' => 2026]));

        $sppt->refresh();
        $this->assertEquals(100000, (float)$sppt->pbb_terutang);
        $this->assertEquals(5000, (float)$sppt->fine_amount); // Fine preserved!
        $this->assertEquals(105000, (float)$sppt->total_bill); // 100000 + 5000 fine
        $this->assertEquals(50000, (float)$sppt->paid_amount); // Paid cash preserved!
        $this->assertEquals('partial', $sppt->status); // Recalculated!
    }

    public function test_store_unified_fails_if_new_bill_is_lower_than_paid_amount(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test3', 'role' => 'admin', 'is_active' => true]);

        $wp = Taxpayer::create(['name' => 'Warga Lunas', 'nik' => '3205019999990002']);
        $obj = TaxObject::create([
            'nop' => '320501000100999002',
            'taxpayer_id' => $wp->id,
            'status' => 'normal',
        ]);

        \App\Models\Sppt::create([
            'tax_object_id' => $obj->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'paid_amount' => 100000,
            'status' => 'paid',
        ]);

        // Attempt to lower bill to 20.000 (below 100.000 already paid)
        $payload = [
            'taxpayer_mode' => 'existing',
            'taxpayer_id' => $wp->id,
            'nop' => $obj->nop,
            'bumi_area_m2' => 30,
            'bumi_njop_per_m2' => 1000000, // 30.000.000 - 10.000.000 = 20.000.000 -> 20.000
            'tax_year' => 2026,
            'njoptkp' => 10000000,
        ];

        $response = $this->actingAs($admin)->post(route('sppts.store-unified'), $payload);
        $response->assertSessionHasErrors('pbb_terutang');
    }
}
