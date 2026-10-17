<?php

namespace Tests\Feature;

use App\Models\BankDeposit;
use App\Models\CashHandover;
use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkHandoverAndCashbookTest extends TestCase
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

    public function test_collector_can_bulk_confirm_submitted_handovers(): void
    {
        $collector = User::factory()->create(['username' => 'collector_test', 'role' => 'kolektor', 'is_active' => true]);
        $cashier = User::factory()->create(['username' => 'cashier_test', 'role' => 'kadus', 'is_active' => true]);

        $h1 = CashHandover::create([
            'handover_number' => 'HO-001',
            'cashier_user_id' => $cashier->id,
            'collector_user_id' => $collector->id,
            'handover_date' => now()->subHours(2),
            'system_amount' => 500000,
            'physical_amount' => 500000,
            'difference_amount' => 0,
            'status' => 'submitted',
        ]);

        $h2 = CashHandover::create([
            'handover_number' => 'HO-002',
            'cashier_user_id' => $cashier->id,
            'collector_user_id' => $collector->id,
            'handover_date' => now()->subHour(),
            'system_amount' => 300000,
            'physical_amount' => 300000,
            'difference_amount' => 0,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($collector)->post(route('handovers.bulk-receive'), [
            'handover_ids' => [$h1->id, $h2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('received', $h1->fresh()->status);
        $this->assertEquals('received', $h2->fresh()->status);
        $this->assertNotNull($h1->fresh()->received_at);
        $this->assertNotNull($h2->fresh()->received_at);
    }

    public function test_cashbook_renders_chronological_ledger_and_running_balance(): void
    {
        $admin = User::factory()->create(['username' => 'admin_test', 'role' => 'admin', 'is_active' => true]);
        $collector = User::factory()->create(['username' => 'collector_test', 'role' => 'kolektor', 'is_active' => true]);
        $cashier = User::factory()->create(['username' => 'cashier_test', 'role' => 'kadus', 'is_active' => true]);

        $wp = Taxpayer::create(['name' => 'Wajib Pajak Uji', 'nik' => '3205010000000099']);
        $op = TaxObject::create(['nop' => '320501000100100999', 'taxpayer_id' => $wp->id]);
        $sppt = Sppt::create([
            'tax_object_id' => $op->id,
            'tax_year' => 2026,
            'pbb_terutang' => 1000000,
            'total_bill' => 1000000,
            'paid_amount' => 500000,
            'status' => 'partial',
        ]);

        // 1. Payment (+500.000)
        Payment::create([
            'receipt_number' => 'KW-20260101-0001',
            'sppt_id' => $sppt->id,
            'cashier_user_id' => $cashier->id,
            'amount_principal' => 500000,
            'amount_fine' => 0,
            'total_amount' => 500000,
            'remaining_after' => 500000,
            'payment_date' => now()->subHours(5),
            'payer_name' => 'Wajib Pajak Uji',
            'status' => 'valid',
            'verification_token' => 'token123',
        ]);

        // 2. Deposit (-200.000)
        BankDeposit::create([
            'deposit_number' => 'DEP-20260101-0001',
            'collector_user_id' => $collector->id,
            'deposit_date' => now()->subHours(2)->toDateString(),
            'destination_bank' => 'Bank BJB',
            'deposit_amount' => 200000,
            'bank_ref_number' => 'BJB-999',
            'status' => 'approved',
            'verifier_user_id' => $admin->id,
            'approved_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($admin)->get(route('cashbook.index'));
        $response->assertStatus(200);
        $response->assertSee('Buku Kas Umum: Jurnal Arus Kas & Saldo Berjalan');
        $response->assertSee('KW-20260101-0001');
        $response->assertSee('DEP-20260101-0001');
        $response->assertSee('+ 500.000');
        $response->assertSee('- 200.000');
        $response->assertSee('300.000'); // 500.000 - 200.000 = 300.000
    }
}
