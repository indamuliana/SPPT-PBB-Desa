<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DusunManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kadus1;
    protected User $kadus2;
    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Desa Sukahaji',
            'active_tax_year' => 2026,
            'target_amount' => 100000000,
            'tax_rate_percent' => 0.100,
        ]);

        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kadus1 = User::factory()->create([
            'name' => 'Kadus Satu',
            'username' => 'kadus1_test',
            'role' => 'kadus',
            'is_active' => true,
        ]);

        $this->kadus2 = User::factory()->create([
            'name' => 'Kadus Dua',
            'username' => 'kadus2_test',
            'role' => 'kadus',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_dusun_index_and_create_page(): void
    {
        Dusun::create([
            'name' => 'Dusun Krajan',
            'code' => 'DS-01',
            'rw' => 'RW 01',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dusuns.index'));
        $response->assertOk();
        $response->assertSee('Dusun Krajan');
        $response->assertSee('DS-01');

        $createResponse = $this->actingAs($this->admin)->get(route('dusuns.create'));
        $createResponse->assertOk();
        $createResponse->assertSee($this->kadus1->name);
    }

    public function test_non_admin_cannot_access_dusun_management(): void
    {
        $response = $this->actingAs($this->kadus1)->get(route('dusuns.index'));
        $response->assertForbidden();

        $createResponse = $this->actingAs($this->kadus1)->get(route('dusuns.create'));
        $createResponse->assertForbidden();
    }

    public function test_admin_can_create_dusun_with_multiple_kadus_assignment(): void
    {
        $payload = [
            'name' => 'Dusun Mekarmulya',
            'code' => 'DS-05',
            'rw' => 'RW 05, RW 06',
            'description' => 'Wilayah pemekaran baru',
            'user_ids' => [$this->kadus1->id, $this->kadus2->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('dusuns.store'), $payload);
        $response->assertRedirect(route('dusuns.index'));

        $dusun = Dusun::where('name', 'Dusun Mekarmulya')->first();
        $this->assertNotNull($dusun);
        $this->assertEquals('DS-05', $dusun->code);
        $this->assertCount(2, $dusun->users);
        $this->assertTrue($dusun->users->contains($this->kadus1));
        $this->assertTrue($dusun->users->contains($this->kadus2));
    }

    public function test_admin_can_update_dusun_and_sync_kadus(): void
    {
        $dusun = Dusun::create([
            'name' => 'Dusun Lama',
            'code' => 'DS-99',
        ]);
        $dusun->users()->sync([$this->kadus1->id]);

        $updatePayload = [
            'name' => 'Dusun Baru Reborn',
            'code' => 'DS-100',
            'rw' => 'RW 10',
            'user_ids' => [$this->kadus2->id], // switch kadus to kadus2
        ];

        $response = $this->actingAs($this->admin)->put(route('dusuns.update', $dusun), $updatePayload);
        $response->assertRedirect(route('dusuns.index'));

        $dusun->refresh();
        $this->assertEquals('Dusun Baru Reborn', $dusun->name);
        $this->assertEquals('DS-100', $dusun->code);
        $this->assertCount(1, $dusun->users);
        $this->assertTrue($dusun->users->contains($this->kadus2));
        $this->assertFalse($dusun->users->contains($this->kadus1));
    }

    public function test_dusun_cannot_be_deleted_if_it_has_tax_objects(): void
    {
        $dusun = Dusun::create(['name' => 'Dusun Berpenghuni']);
        $taxpayer = Taxpayer::create(['name' => 'Warga Test']);

        TaxObject::create([
            'nop' => '321205000100999990',
            'taxpayer_id' => $taxpayer->id,
            'dusun_id' => $dusun->id,
            'dusun' => $dusun->name,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('dusuns.destroy', $dusun));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('dusuns', ['id' => $dusun->id]);
    }

    public function test_admin_can_delete_clean_dusun(): void
    {
        $dusun = Dusun::create(['name' => 'Dusun Kosong']);
        $dusun->users()->sync([$this->kadus1->id]);

        $response = $this->actingAs($this->admin)->delete(route('dusuns.destroy', $dusun));
        $response->assertRedirect(route('dusuns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('dusuns', ['id' => $dusun->id]);
        $this->assertDatabaseMissing('dusun_user', ['dusun_id' => $dusun->id]);
    }

    public function test_admin_can_delete_dusun_with_tax_objects_by_reassigning(): void
    {
        $dusunOld = Dusun::create(['name' => 'Dusun Gusuran']);
        $dusunTarget = Dusun::create(['name' => 'Dusun Penampung']);
        $taxpayer = Taxpayer::create(['name' => 'Warga Pindahan']);

        $obj = TaxObject::create([
            'nop' => '321205000100888880',
            'taxpayer_id' => $taxpayer->id,
            'dusun_id' => $dusunOld->id,
            'dusun' => $dusunOld->name,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('dusuns.destroy', $dusunOld), [
            'action_on_objects' => 'reassign',
            'target_dusun_id' => $dusunTarget->id,
        ]);

        $response->assertRedirect(route('dusuns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('dusuns', ['id' => $dusunOld->id]);
        $obj->refresh();
        $this->assertEquals($dusunTarget->id, $obj->dusun_id);
        $this->assertEquals($dusunTarget->name, $obj->dusun);
    }

    public function test_admin_can_delete_dusun_with_tax_objects_by_detaching(): void
    {
        $dusun = Dusun::create(['name' => 'Dusun Dihapus']);
        $taxpayer = Taxpayer::create(['name' => 'Warga Mandiri']);

        $obj = TaxObject::create([
            'nop' => '321205000100888881',
            'taxpayer_id' => $taxpayer->id,
            'dusun_id' => $dusun->id,
            'dusun' => $dusun->name,
            'status' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('dusuns.destroy', $dusun), [
            'action_on_objects' => 'detach',
        ]);

        $response->assertRedirect(route('dusuns.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('dusuns', ['id' => $dusun->id]);
        $obj->refresh();
        $this->assertNull($obj->dusun_id);
        $this->assertNull($obj->dusun);
    }

    public function test_pos_smart_filtering_for_assigned_kadus(): void
    {
        $dusun1 = Dusun::create(['name' => 'Dusun Binaan']);
        $dusun2 = Dusun::create(['name' => 'Dusun Luar']);

        // Assign Kadus1 to Dusun1
        $this->kadus1->dusuns()->sync([$dusun1->id]);

        $taxpayer = Taxpayer::create(['name' => 'Warga 1']);

        // Object 1 in Dusun1
        $obj1 = TaxObject::create([
            'nop' => '321205000100111110',
            'taxpayer_id' => $taxpayer->id,
            'dusun_id' => $dusun1->id,
            'dusun' => $dusun1->name,
            'status' => 'normal',
        ]);
        $sppt1 = Sppt::create([
            'tax_object_id' => $obj1->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'status' => 'unpaid',
        ]);

        // Object 2 in Dusun2
        $obj2 = TaxObject::create([
            'nop' => '321205000100222220',
            'taxpayer_id' => $taxpayer->id,
            'dusun_id' => $dusun2->id,
            'dusun' => $dusun2->name,
            'status' => 'normal',
        ]);
        $sppt2 = Sppt::create([
            'tax_object_id' => $obj2->id,
            'tax_year' => 2026,
            'pbb_terutang' => 150000,
            'total_bill' => 150000,
            'status' => 'unpaid',
        ]);

        // 1. Kadus accesses POS without filter parameter -> defaults to 'my' dusun
        $responseDefault = $this->actingAs($this->kadus1)->get(route('payments.pos'));
        $responseDefault->assertOk();
        $responseDefault->assertSee($obj1->formatted_nop);
        $responseDefault->assertDontSee($obj2->formatted_nop);
        $responseDefault->assertSee('Wilayah Saya');

        // 2. Kadus switches filter to 'all' -> sees both SPPTs
        $responseAll = $this->actingAs($this->kadus1)->get(route('payments.pos', ['dusun' => 'all']));
        $responseAll->assertOk();
        $responseAll->assertSee($obj1->formatted_nop);
        $responseAll->assertSee($obj2->formatted_nop);
    }
}
