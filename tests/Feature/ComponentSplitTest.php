<?php

namespace Tests\Feature;

use App\Models\Sppt;
use App\Models\SpptMutation;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentSplitTest extends TestCase
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

    public function test_user_can_propose_and_admin_approves_component_split(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test', 'role' => 'admin', 'is_active' => true]);
        $kadus = User::factory()->create(['username' => 'kadus_test', 'role' => 'kadus', 'is_active' => true]);

        $wp1 = Taxpayer::create(['name' => 'Wajib Pajak Pemilik Awal', 'nik' => '3205010000000001']);
        $wp2 = Taxpayer::create(['name' => 'Wajib Pajak Pembeli Bagian', 'nik' => '3205010000000002']);

        $obj1 = TaxObject::create([
            'nop' => '320501000100100010',
            'taxpayer_id' => $wp1->id,
            'address' => 'Jl. Blok Sawah',
            'dusun' => 'Dusun 1',
        ]);

        $compBumi = TaxObjectComponent::create([
            'tax_object_id' => $obj1->id,
            'type' => 'bumi',
            'area_m2' => 500,
            'class_code' => 'A35',
            'njop_per_m2' => 100000,
            'total_njop' => 50000000,
            'tax_year' => 2026,
        ]);

        Sppt::create([
            'tax_object_id' => $obj1->id,
            'tax_year' => 2026,
            'njop_dasar' => 50000000,
            'njoptkp' => 10000000,
            'njop_perhitungan' => 40000000,
            'tarif_percent' => 0.100,
            'pbb_terutang' => 40000,
            'total_bill' => 40000,
            'status' => 'unpaid',
        ]);

        // 1. Kadus proposes split of 200 m2
        $response = $this->actingAs($kadus)->post(route('mutations.store-split-component'), [
            'tax_object_id' => $obj1->id,
            'split_component_type' => 'bumi',
            'split_area_m2' => 200,
            'new_taxpayer_id' => $wp2->id,
            'legal_doc_type' => 'AJB',
            'legal_doc_number' => '123/AJB/2026',
            'reason' => 'Dijual sebagian tanah 200 m2 ke WP 2',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sppt_mutations', [
            'tax_object_id' => $obj1->id,
            'mutation_type' => 'pemecahan',
            'split_component_type' => 'bumi',
            'split_area_m2' => 200,
            'new_taxpayer_id' => $wp2->id,
            'status' => 'pending',
        ]);

        $mutation = SpptMutation::where('tax_object_id', $obj1->id)->first();

        // 2. Admin approves mutation
        $approveResponse = $this->actingAs($admin)->post(route('mutations.approve', $mutation));
        $approveResponse->assertRedirect();

        // 3. Verify source component was reduced (500 - 200 = 300)
        $compBumi->refresh();
        $this->assertEquals(300, (float)$compBumi->area_m2);
        $this->assertEquals(30000000, (float)$compBumi->total_njop);

        // 4. Verify target TaxObject was created with 200 m2 component
        $mutation->refresh();
        $this->assertNotNull($mutation->target_tax_object_id);
        $targetObject = TaxObject::find($mutation->target_tax_object_id);
        $this->assertEquals($wp2->id, $targetObject->taxpayer_id);

        $targetComp = TaxObjectComponent::where('tax_object_id', $targetObject->id)->where('type', 'bumi')->first();
        $this->assertNotNull($targetComp);
        $this->assertEquals(200, (float)$targetComp->area_m2);
        $this->assertEquals(20000000, (float)$targetComp->total_njop);

        // 5. Verify SPPT recalculated for both
        $sourceSppt = Sppt::where('tax_object_id', $obj1->id)->where('tax_year', 2026)->first();
        $this->assertEquals(30000000, (float)$sourceSppt->njop_dasar);
        $this->assertEquals(20000, (float)$sourceSppt->total_bill); // (30.000.000 - 10.000.000) * 0.1% = 20.000

        $targetSppt = Sppt::where('tax_object_id', $targetObject->id)->where('tax_year', 2026)->first();
        $this->assertEquals(20000000, (float)$targetSppt->njop_dasar);
        $this->assertEquals(10000, (float)$targetSppt->total_bill); // (20.000.000 - 10.000.000) * 0.1% = 10.000
    }
}
