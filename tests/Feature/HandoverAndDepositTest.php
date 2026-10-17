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

class HandoverAndDepositTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kadus;
    private User $kolektor;
    private Sppt $sppt;

    protected function setUp(): void
    {
        parent::setUp();

        Village::create([
            'name' => 'Sukahaji',
            'district_name' => 'Sukahaji',
            'regency_name' => 'Majalengka',
            'target_pbb_amount' => 100000000,
            'active_tax_year' => 2026,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Desa',
            'username' => 'admin',
            'email' => 'admin@desa.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->kadus = User::create([
            'name' => 'Kadus Satu',
            'username' => 'kadus1',
            'email' => 'kadus1@desa.id',
            'password' => bcrypt('password'),
            'role' => 'kadus',
            'dusun' => 'Dusun I',
            'is_active' => true,
        ]);

        $this->kolektor = User::create([
            'name' => 'Kolektor Desa',
            'username' => 'kolektor',
            'email' => 'kolektor@desa.id',
            'password' => bcrypt('password'),
            'role' => 'kolektor',
            'is_active' => true,
        ]);

        $taxpayer = Taxpayer::create([
            'name' => 'Bapak Ahmad',
            'address' => 'RT 01 RW 01',
        ]);

        $taxObject = TaxObject::create([
            'nop' => '321205000100100010',
            'taxpayer_id' => $taxpayer->id,
            'address' => 'Blok Desa',
            'dusun' => 'Dusun I',
        ]);

        $this->sppt = Sppt::create([
            'tax_object_id' => $taxObject->id,
            'tax_year' => 2026,
            'pbb_amount' => 150000,
            'status' => 'unpaid',
            'due_date' => '2026-09-30',
        ]);
    }

    public function test_cashier_can_create_handover_with_unhanded_payments()
    {
        // 1. Kadus records payment
        $payment = Payment::create([
            'sppt_id' => $this->sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'receipt_number' => Payment::generateReceiptNumber(2026),
            'verification_token' => Payment::generateVerificationToken(),
            'payment_date' => now(),
            'amount_principal' => 150000,
            'amount_fine' => 0,
            'total_amount' => 150000,
            'method' => 'cash',
            'status' => 'valid',
        ]);

        // 2. Kadus opens handover create page
        $res = $this->actingAs($this->kadus)->get(route('handovers.create'));
        $res->assertStatus(200);
        $res->assertSee($payment->receipt_number);

        // 3. Kadus submits handover to Kolektor
        $response = $this->actingAs($this->kadus)->post(route('handovers.store'), [
            'collector_user_id' => $this->kolektor->id,
            'payment_ids' => [$payment->id],
            'physical_amount' => 150000,
            'notes' => 'Setoran pagi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_handovers', [
            'cashier_user_id' => $this->kadus->id,
            'collector_user_id' => $this->kolektor->id,
            'system_amount' => 150000,
            'physical_amount' => 150000,
            'difference_amount' => 0,
            'status' => 'submitted',
        ]);

        $this->assertNotNull($payment->fresh()->handover_id);
    }

    public function test_handover_requires_difference_reason_when_variance_exists()
    {
        $payment = Payment::create([
            'sppt_id' => $this->sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'receipt_number' => Payment::generateReceiptNumber(2026),
            'verification_token' => Payment::generateVerificationToken(),
            'payment_date' => now(),
            'amount_principal' => 150000,
            'amount_fine' => 0,
            'total_amount' => 150000,
            'method' => 'cash',
            'status' => 'valid',
        ]);

        // Attempt submission with physical != system without reason
        $res = $this->actingAs($this->kadus)->post(route('handovers.store'), [
            'collector_user_id' => $this->kolektor->id,
            'payment_ids' => [$payment->id],
            'physical_amount' => 140000, // Rp 10.000 difference
            'difference_reason' => '', // Empty reason
        ]);

        $res->assertSessionHasErrors('difference_reason');
    }

    public function test_collector_can_confirm_handover_receipt()
    {
        $payment = Payment::create([
            'sppt_id' => $this->sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'receipt_number' => Payment::generateReceiptNumber(2026),
            'verification_token' => Payment::generateVerificationToken(),
            'payment_date' => now(),
            'amount_principal' => 150000,
            'amount_fine' => 0,
            'total_amount' => 150000,
            'method' => 'cash',
            'status' => 'valid',
        ]);

        $handover = CashHandover::create([
            'handover_number' => CashHandover::generateHandoverNumber(),
            'cashier_user_id' => $this->kadus->id,
            'collector_user_id' => $this->kolektor->id,
            'handover_date' => now(),
            'system_amount' => 150000,
            'physical_amount' => 150000,
            'difference_amount' => 0,
            'status' => 'submitted',
        ]);
        $payment->update(['handover_id' => $handover->id]);

        // Kolektor confirms
        $res = $this->actingAs($this->kolektor)->post(route('handovers.receive', $handover));
        $res->assertSessionHas('success');
        $this->assertTrue($handover->fresh()->isReceived());
        $this->assertNotNull($handover->fresh()->received_at);
    }

    public function test_collector_cannot_deposit_more_than_available_cash()
    {
        // Handover received by kolektor is 150.000
        CashHandover::create([
            'handover_number' => CashHandover::generateHandoverNumber(),
            'cashier_user_id' => $this->kadus->id,
            'collector_user_id' => $this->kolektor->id,
            'handover_date' => now(),
            'system_amount' => 150000,
            'physical_amount' => 150000,
            'difference_amount' => 0,
            'status' => 'received',
            'received_at' => now(),
        ]);

        // Kolektor attempts to deposit 200.000 (exceeds 150.000)
        $res = $this->actingAs($this->kolektor)->post(route('deposits.store'), [
            'deposit_date' => '2026-05-10',
            'destination_bank' => 'Bank bjb Cabang Majalengka',
            'deposit_amount' => 200000,
        ]);

        $res->assertSessionHasErrors('deposit_amount');
        $this->assertDatabaseMissing('bank_deposits', [
            'deposit_amount' => 200000,
        ]);
    }

    public function test_collector_records_deposit_and_admin_approves_it()
    {
        CashHandover::create([
            'handover_number' => CashHandover::generateHandoverNumber(),
            'cashier_user_id' => $this->kadus->id,
            'collector_user_id' => $this->kolektor->id,
            'handover_date' => now(),
            'system_amount' => 150000,
            'physical_amount' => 150000,
            'difference_amount' => 0,
            'status' => 'received',
            'received_at' => now(),
        ]);

        // 1. Kolektor records deposit of 150.000
        $res = $this->actingAs($this->kolektor)->post(route('deposits.store'), [
            'deposit_date' => '2026-05-10',
            'destination_bank' => 'Bank bjb Cabang Majalengka',
            'deposit_amount' => 150000,
            'bank_ref_number' => 'BJB-998877',
        ]);

        $res->assertRedirect(route('deposits.index'));
        $deposit = BankDeposit::where('bank_ref_number', 'BJB-998877')->first();
        $this->assertNotNull($deposit);
        $this->assertEquals('submitted', $deposit->status);

        // 2. Admin approves deposit
        $approveRes = $this->actingAs($this->admin)->post(route('deposits.approve', $deposit));
        $approveRes->assertSessionHas('success');
        $this->assertTrue($deposit->fresh()->isApproved());
        $this->assertEquals($this->admin->id, $deposit->fresh()->verifier_user_id);
    }

    public function test_cashbook_reconciliation_renders_correctly()
    {
        $res = $this->actingAs($this->admin)->get(route('cashbook.index'));
        $res->assertStatus(200);
        $res->assertSee('Buku Kas');
        $res->assertSee('SEIMBANG');
    }
}
