<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kadus;
    protected Village $village;
    protected Taxpayer $taxpayer;
    protected TaxObject $normalOp;
    protected TaxObject $toOp;
    protected Sppt $spptNormal;
    protected Sppt $spptTo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
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

        $this->taxpayer = Taxpayer::create([
            'name' => 'Haji Sukarna Wiraatmadja',
            'nik' => '3212051203650001',
            'dusun' => 'Dusun I Babakan',
        ]);

        $this->normalOp = TaxObject::create([
            'nop' => '321205000100100010',
            'taxpayer_id' => $this->taxpayer->id,
            'status' => 'normal',
            'dusun' => 'Dusun I Babakan',
        ]);

        $this->toOp = TaxObject::create([
            'nop' => '321205000100200020',
            'taxpayer_id' => $this->taxpayer->id,
            'status' => 'to', // Tanpa Objek / Pemilik Belum Jelas
            'dusun' => 'Dusun II Sukajaya',
        ]);

        $this->spptNormal = Sppt::create([
            'tax_object_id' => $this->normalOp->id,
            'tax_year' => 2026,
            'pbb_terutang' => 125000,
            'total_bill' => 125000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->spptTo = Sppt::create([
            'tax_object_id' => $this->toOp->id,
            'tax_year' => 2026,
            'pbb_terutang' => 65000,
            'total_bill' => 65000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    public function test_cashier_can_access_pos_and_search_nop(): void
    {
        $response = $this->actingAs($this->kadus)->get('/pos?search=321205000100100010');
        $response->assertStatus(200);
        $response->assertSee('Haji Sukarna Wiraatmadja');
        $response->assertSee('125.000');
    }

    public function test_cashier_can_process_full_payment(): void
    {
        $response = $this->actingAs($this->kadus)->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 125000,
            'amount_fine' => 0,
            'notes' => 'Pembayaran lunas tunai',
        ]);

        $this->spptNormal->refresh();
        $this->assertTrue($this->spptNormal->isPaid());
        $this->assertEquals(125000, $this->spptNormal->paid_amount);
        $this->assertEquals(0, $this->spptNormal->remaining_bill);

        $payment = Payment::where('sppt_id', $this->spptNormal->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(125000, $payment->total_amount);
        $this->assertEquals(0, $payment->remaining_after);
        $this->assertEquals('valid', $payment->status);

        $response->assertRedirect(route('payments.receipt', $payment));
    }

    public function test_cashier_can_process_partial_payment(): void
    {
        $response = $this->actingAs($this->kadus)->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 50000,
            'amount_fine' => 0,
            'notes' => 'Cicilan pertama',
        ]);

        $this->spptNormal->refresh();
        $this->assertTrue($this->spptNormal->isPartial());
        $this->assertEquals(50000, $this->spptNormal->paid_amount);
        $this->assertEquals(75000, $this->spptNormal->remaining_bill);

        $payment = Payment::where('sppt_id', $this->spptNormal->id)->first();
        $this->assertEquals(50000, $payment->total_amount);
        $this->assertEquals(75000, $payment->remaining_after);
    }

    public function test_payment_cannot_exceed_remaining_bill(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->actingAs($this->kadus)->withoutExceptionHandling()->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 200000, // exceeds 125,000
        ]);
    }

    public function test_payment_is_blocked_for_sppt_with_to_status(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Attempting to pay an object flagged as TO must be rejected
        $this->actingAs($this->kadus)->withoutExceptionHandling()->post('/payments', [
            'sppt_id' => $this->spptTo->id,
            'amount_principal' => 65000,
        ]);
    }

    public function test_receipt_can_be_rendered_in_a4_and_thermal_formats(): void
    {
        $this->actingAs($this->kadus)->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 125000,
        ]);

        $payment = Payment::where('sppt_id', $this->spptNormal->id)->first();

        // A4 format
        $responseA4 = $this->actingAs($this->kadus)->get("/payments/{$payment->id}/receipt?format=a4");
        $responseA4->assertStatus(200);
        $responseA4->assertSee('LEMBAR WAJIB PAJAK');
        $responseA4->assertSee('LEMBAR ARSIP PEMERINTAH DESA');

        // Thermal format
        $responseThermal = $this->actingAs($this->kadus)->get("/payments/{$payment->id}/receipt?format=thermal");
        $responseThermal->assertStatus(200);
        $responseThermal->assertSee('*** LUNAS ***');
    }

    public function test_public_qr_verification_endpoint(): void
    {
        $this->actingAs($this->kadus)->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 125000,
        ]);

        $payment = Payment::where('sppt_id', $this->spptNormal->id)->first();

        $response = $this->get("/verifikasi/{$payment->verification_token}");
        $response->assertStatus(200);
        $response->assertSee('DOKUMEN RESMI DAN SAH');
        $response->assertSee('H*** S****** W**********');
        $response->assertDontSee('Haji Sukarna Wiraatmadja');
    }

    public function test_cancellation_workflow_and_reversal(): void
    {
        // 1. Pay
        $this->actingAs($this->kadus)->post('/payments', [
            'sppt_id' => $this->spptNormal->id,
            'amount_principal' => 125000,
        ]);
        $payment = Payment::where('sppt_id', $this->spptNormal->id)->first();

        // 2. Kasir requests cancellation
        $responseReq = $this->actingAs($this->kadus)->post("/payments/{$payment->id}/cancel", [
            'reason' => 'Salah input identitas pembayar',
        ]);
        $responseReq->assertSessionHas('success');

        $payment->refresh();
        $this->assertTrue($payment->isCancellationRequested());

        // 3. Admin approves cancellation
        $responseApprove = $this->actingAs($this->admin)->post("/payments/{$payment->id}/cancel/approve");
        $responseApprove->assertSessionHas('success');

        $payment->refresh();
        $this->spptNormal->refresh();

        $this->assertTrue($payment->isCancelled());
        $this->assertEquals(0, $this->spptNormal->paid_amount);
        $this->assertTrue($this->spptNormal->isUnpaid());
        $this->assertEquals(125000, $this->spptNormal->remaining_bill);

        // 4. Public QR page now shows cancelled status
        $responsePublic = $this->get("/verifikasi/{$payment->verification_token}");
        $responsePublic->assertStatus(200);
        $responsePublic->assertSee('DOKUMEN TELAH DIBATALKAN');
    }

    public function test_invalid_qr_token_shows_not_found(): void
    {
        $response = $this->get('/verifikasi/invalid-fake-token-12345');
        $response->assertStatus(200);
        $response->assertSee('Kuitansi Tidak Ditemukan');
    }
}
