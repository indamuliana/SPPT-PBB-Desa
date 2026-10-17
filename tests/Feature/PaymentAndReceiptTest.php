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

    public function test_cashier_can_select_sppt_from_table_in_pos(): void
    {
        $response = $this->actingAs($this->kadus)->get('/pos?selected_sppt_id=' . $this->spptNormal->id);
        $response->assertStatus(200);
        $response->assertSee('Haji Sukarna Wiraatmadja');
        $response->assertSee('125.000');
        $response->assertSee('Terima Pembayaran & Cetak Kuitansi', false);
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

    public function test_cashier_can_quick_edit_taxpayer_taxobject_and_sppt_from_pos(): void
    {
        $response = $this->actingAs($this->kadus)->put("/pos/quick-edit/{$this->spptNormal->id}", [
            'taxpayer_name' => 'Haji Sukarna Wiraatmadja S.E.',
            'taxpayer_nik' => '3212051203650099',
            'taxpayer_phone' => '08123456789',
            'taxpayer_address' => 'Jl. Sindanglaut No. 12',
            'nop' => '321205000100100099',
            'object_address' => 'Blok Desa Kidul RT 02 RW 01',
            'object_rt' => '002',
            'object_rw' => '001',
            'bumi_area_m2' => 150,
            'bumi_njop_per_m2' => 200000,
            'bangunan_area_m2' => 60,
            'bangunan_njop_per_m2' => 500000,
            'pbb_terutang' => 180000,
            'due_date' => '2026-10-31',
            'dhkp_row_ref' => 'DHKP-001',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->taxpayer->refresh();
        $this->assertEquals('Haji Sukarna Wiraatmadja S.E.', $this->taxpayer->name);
        $this->assertEquals('3212051203650099', $this->taxpayer->nik);
        $this->assertEquals('08123456789', $this->taxpayer->phone);

        $this->normalOp->refresh();
        $this->assertEquals('321205000100100099', $this->normalOp->nop);
        $this->assertEquals('002', $this->normalOp->rt);
        $this->assertEquals('001', $this->normalOp->rw);

        $this->spptNormal->refresh();
        $this->assertEquals(180000, $this->spptNormal->pbb_terutang);
        $this->assertEquals(180000, $this->spptNormal->total_bill);
        $this->assertEquals('2026-10-31', $this->spptNormal->due_date->format('Y-m-d'));
        $this->assertEquals('DHKP-001', $this->spptNormal->dhkp_row_ref);

        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $this->normalOp->id,
            'type' => 'bumi',
            'area_m2' => 150,
            'njop_per_m2' => 200000,
            'total_njop' => 30000000,
        ]);
        $this->assertDatabaseHas('tax_object_components', [
            'tax_object_id' => $this->normalOp->id,
            'type' => 'bangunan',
            'area_m2' => 60,
            'njop_per_m2' => 500000,
            'total_njop' => 30000000,
        ]);
    }

    public function test_quick_edit_prevents_lowering_bill_below_paid_amount(): void
    {
        $this->spptNormal->update([
            'paid_amount' => 50000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($this->kadus)->put("/pos/quick-edit/{$this->spptNormal->id}", [
            'taxpayer_name' => $this->taxpayer->name,
            'nop' => $this->normalOp->nop,
            'pbb_terutang' => 30000, // lower than paid_amount (50000)
        ]);

        $response->assertSessionHas('error');
        $this->spptNormal->refresh();
        $this->assertEquals(125000, $this->spptNormal->pbb_terutang);
    }

    public function test_quick_edit_prevents_duplicate_nop_on_different_tax_object(): void
    {
        $response = $this->actingAs($this->kadus)->put("/pos/quick-edit/{$this->spptNormal->id}", [
            'taxpayer_name' => $this->taxpayer->name,
            'nop' => $this->toOp->nop, // duplicate with toOp
            'pbb_terutang' => 125000,
        ]);

        $response->assertSessionHas('error');
        $this->normalOp->refresh();
        $this->assertNotEquals($this->toOp->nop, $this->normalOp->nop);
    }
}
