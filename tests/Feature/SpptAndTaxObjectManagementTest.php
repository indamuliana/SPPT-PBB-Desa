<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpptAndTaxObjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kadus;
    protected Village $village;
    protected Taxpayer $taxpayer;
    protected TaxObject $taxObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Desa Sukahaji',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
        ]);

        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kadus = User::factory()->create([
            'username' => 'kadus_test',
            'role' => 'kadus',
            'is_active' => true,
        ]);

        $this->taxpayer = Taxpayer::create([
            'name' => 'Ahmad Dahlan',
            'nik' => '3205010000000099',
            'phone' => '08123456789',
            'address' => 'Kp. Sukahaji',
            'dusun' => 'Dusun 1',
        ]);

        $this->taxObject = TaxObject::create([
            'nop' => '320501000100100099',
            'taxpayer_id' => $this->taxpayer->id,
            'address' => 'Blok Pasir Muncang',
            'dusun' => 'Dusun 1',
            'status' => 'normal',
        ]);
    }

    public function test_admin_can_view_sppt_edit_page_and_update_it(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'njop_dasar' => 60000000,
            'njoptkp' => 10000000,
            'njop_perhitungan' => 50000000,
            'tarif_percent' => 0.100,
            'pbb_terutang' => 50000,
            'total_bill' => 50000,
            'status' => 'unpaid',
            'due_date' => '2026-09-30',
        ]);

        $response = $this->actingAs($this->admin)->get(route('sppts.edit', $sppt));
        $response->assertStatus(200);
        $response->assertSee('Edit Ketetapan SPPT');

        $updateResponse = $this->actingAs($this->admin)->put(route('sppts.update', $sppt), [
            'pbb_terutang' => 65000,
            'due_date' => '2026-10-31',
            'dhkp_row_ref' => '102/B',
        ]);

        $updateResponse->assertRedirect(route('sppts.index', ['year' => 2026]));
        $sppt->refresh();
        $this->assertEquals(65000, (float)$sppt->pbb_terutang);
        $this->assertEquals(65000, (float)$sppt->total_bill);
        $this->assertEquals('2026-10-31', $sppt->due_date->format('Y-m-d'));
        $this->assertEquals('102/B', $sppt->dhkp_row_ref);
    }

    public function test_admin_cannot_update_sppt_total_bill_below_paid_amount(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'paid_amount' => 60000,
            'status' => 'partial',
        ]);

        $updateResponse = $this->actingAs($this->admin)->put(route('sppts.update', $sppt), [
            'pbb_terutang' => 40000, // below paid 60,000!
            'due_date' => '2026-09-30',
        ]);

        $updateResponse->assertSessionHasErrors(['pbb_terutang']);
        $sppt->refresh();
        $this->assertEquals(100000, (float)$sppt->pbb_terutang);
    }

    public function test_admin_cannot_delete_sppt_with_payment_history(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'paid_amount' => 100000,
            'status' => 'paid',
        ]);

        Payment::create([
            'receipt_number' => Payment::generateReceiptNumber(),
            'verification_token' => Str::random(32),
            'sppt_id' => $sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'amount_principal' => 100000,
            'amount_fine' => 0,
            'total_amount' => 100000,
            'remaining_after' => 0,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'valid',
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('sppts.destroy', $sppt));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('sppts', ['id' => $sppt->id]);
    }

    public function test_admin_can_delete_clean_sppt_without_payments(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 75000,
            'total_bill' => 75000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('sppts.destroy', $sppt));
        $deleteResponse->assertRedirect(route('sppts.index', ['year' => 2026]));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('sppts', ['id' => $sppt->id]);
    }

    public function test_user_can_view_tax_object_show_page(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 50000,
            'total_bill' => 50000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->kadus)->get(route('tax-objects.show', $this->taxObject));
        $response->assertStatus(200);
        $response->assertSee($this->taxObject->formatted_nop);
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('Rp 50.000');
    }

    public function test_admin_can_deactivate_and_activate_tax_object(): void
    {
        $deactivateResponse = $this->actingAs($this->admin)
            ->from(route('tax-objects.show', $this->taxObject))
            ->post(route('tax-objects.deactivate', $this->taxObject), [
                'reason' => 'mutasi',
                'notes' => 'Telah diajukan berkas mutasi ke Bapenda',
            ]);

        $deactivateResponse->assertRedirect(route('tax-objects.show', $this->taxObject));
        $this->taxObject->refresh();
        $this->assertTrue($this->taxObject->isInactive());
        $this->assertEquals('mutasi', $this->taxObject->deactivation_reason);

        $activateResponse = $this->actingAs($this->admin)
            ->from(route('tax-objects.show', $this->taxObject))
            ->post(route('tax-objects.activate', $this->taxObject));

        $activateResponse->assertRedirect(route('tax-objects.show', $this->taxObject));
        $this->taxObject->refresh();
        $this->assertEquals('normal', $this->taxObject->status);
        $this->assertNull($this->taxObject->deactivation_reason);
    }

    public function test_admin_cannot_delete_tax_object_with_payment_history(): void
    {
        $sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 100000,
            'total_bill' => 100000,
            'paid_amount' => 100000,
            'status' => 'paid',
        ]);

        Payment::create([
            'receipt_number' => Payment::generateReceiptNumber(),
            'verification_token' => Str::random(32),
            'sppt_id' => $sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'amount_principal' => 100000,
            'amount_fine' => 0,
            'total_amount' => 100000,
            'remaining_after' => 0,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'valid',
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('tax-objects.destroy', $this->taxObject));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('tax_objects', ['id' => $this->taxObject->id]);
    }

    public function test_admin_can_delete_clean_tax_object_without_payments(): void
    {
        $cleanTaxObject = TaxObject::create([
            'nop' => '320501000100100088',
            'taxpayer_id' => $this->taxpayer->id,
            'address' => 'Blok Lapang Baru',
            'status' => 'normal',
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('tax-objects.destroy', $cleanTaxObject));
        $deleteResponse->assertRedirect(route('tax-objects.index'));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('tax_objects', ['id' => $cleanTaxObject->id]);
    }

    public function test_api_tax_objects_search_returns_suggestions(): void
    {
        $response = $this->actingAs($this->kadus)->getJson('/api/tax-objects/search?q=Ahmad');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'taxpayer_name' => 'Ahmad Dahlan',
            'formatted_nop' => $this->taxObject->formatted_nop,
        ]);

        $responseNop = $this->actingAs($this->kadus)->getJson('/api/tax-objects/search?q=320501000100100099');
        $responseNop->assertStatus(200);
        $responseNop->assertJsonFragment([
            'taxpayer_name' => 'Ahmad Dahlan',
        ]);
    }
}
