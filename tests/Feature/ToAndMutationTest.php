<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Sppt;
use App\Models\SpptMutation;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\ToCase;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToAndMutationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kadus;
    private User $kolektor;
    private Taxpayer $oldWp;
    private Taxpayer $newWp;
    private TaxObject $taxObject;
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

        $this->oldWp = Taxpayer::create([
            'name' => 'Bapak Subur (Mantan Pemilik)',
            'address' => 'RT 01 RW 01',
        ]);

        $this->newWp = Taxpayer::create([
            'name' => 'Ibu Ratna (Pembeli Baru)',
            'address' => 'RT 02 RW 01',
        ]);

        $this->taxObject = TaxObject::create([
            'nop' => '321205000100100099',
            'taxpayer_id' => $this->oldWp->id,
            'address' => 'Blok Pasir Gede',
            'dusun' => 'Dusun I',
            'status' => 'normal',
        ]);

        $this->sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_amount' => 250000,
            'total_bill' => 250000,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'is_to_blocked' => false,
            'due_date' => '2026-09-30',
        ]);
    }

    public function test_user_can_report_to_case_which_locks_nop_and_sppts_from_pos()
    {
        // 1. Kadus reports NOP as TO
        $res = $this->actingAs($this->kadus)->post(route('to-cases.store'), [
            'nop' => $this->taxObject->nop,
            'reason_type' => 'pemilik_tidak_dikenal',
            'description' => 'Tanah ditinggalkan sejak lama dan tetangga tidak tahu keberadaan pemilik.',
            'assigned_to_user_id' => $this->kolektor->id,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('to_cases', [
            'tax_object_id' => $this->taxObject->id,
            'reason_type' => 'pemilik_tidak_dikenal',
            'status' => 'open',
        ]);

        // Verify TaxObject is marked TO and SPPT is locked
        $this->assertEquals('to', $this->taxObject->fresh()->status);
        $this->assertTrue((bool)$this->sppt->fresh()->is_to_blocked);

        // 2. Kasir POS tries to make payment on this SPPT -> MUST BE REJECTED
        $this->expectException(\InvalidArgumentException::class);

        $this->actingAs($this->kadus)->withoutExceptionHandling()->post(route('payments.store'), [
            'sppt_id' => $this->sppt->id,
            'amount_principal' => 250000,
            'payment_date' => date('Y-m-d'),
        ]);
    }

    public function test_collector_can_update_investigation_log_for_to_case()
    {
        $toCase = ToCase::create([
            'case_number' => ToCase::generateCaseNumber(),
            'tax_object_id' => $this->taxObject->id,
            'reported_by_user_id' => $this->kadus->id,
            'assigned_to_user_id' => $this->kolektor->id,
            'reason_type' => 'pemilik_tidak_dikenal',
            'description' => 'Pemilik awal tidak jelas.',
            'status' => 'open',
        ]);

        $res = $this->actingAs($this->kolektor)->post(route('to-cases.investigation', $toCase), [
            'status' => 'investigating',
            'new_notes' => 'Telah berkunjung ke ketua RT. Dikonfirmasi bahwa tanah sudah dijual ke Ibu Ratna.',
        ]);

        $res->assertSessionHas('success');
        $this->assertEquals('investigating', $toCase->fresh()->status);
        $this->assertStringContainsString('Ibu Ratna', $toCase->fresh()->investigation_notes);
    }

    public function test_resolving_to_case_unblocks_tax_object_and_sppt_for_payment()
    {
        $this->taxObject->update(['status' => 'to']);
        $this->sppt->update(['is_to_blocked' => true]);

        $toCase = ToCase::create([
            'case_number' => ToCase::generateCaseNumber(),
            'tax_object_id' => $this->taxObject->id,
            'reported_by_user_id' => $this->kadus->id,
            'assigned_to_user_id' => $this->kolektor->id,
            'reason_type' => 'pemilik_tidak_dikenal',
            'description' => 'Pemilik awal tidak jelas.',
            'status' => 'investigating',
        ]);

        // Resolve case
        $res = $this->actingAs($this->kolektor)->post(route('to-cases.investigation', $toCase), [
            'status' => 'resolved',
            'new_notes' => 'Kasus selesai diteliti. Pemilik telah terverifikasi dan siap membayar.',
        ]);

        $res->assertSessionHas('success');
        $this->assertEquals('resolved', $toCase->fresh()->status);
        $this->assertNotNull($toCase->fresh()->resolved_at);

        // Tax object must return to normal and SPPT unblocked!
        $this->assertEquals('normal', $this->taxObject->fresh()->status);
        $this->assertFalse((bool)$this->sppt->fresh()->is_to_blocked);

        // Kasir POS can now process payment without exception!
        $payRes = $this->actingAs($this->kadus)->post(route('payments.store'), [
            'sppt_id' => $this->sppt->id,
            'amount_principal' => 250000,
            'payment_date' => date('Y-m-d'),
        ]);

        $payRes->assertRedirect();
        $this->assertEquals(250000, (float)$this->sppt->fresh()->paid_amount);
        $this->assertEquals('paid', $this->sppt->fresh()->status);
    }

    public function test_user_can_propose_mutation_and_prevent_same_taxpayer()
    {
        // 1. Trying to mutate with same old and new taxpayer fails
        $failRes = $this->actingAs($this->kadus)->post(route('mutations.store'), [
            'nop' => $this->taxObject->nop,
            'mutation_type' => 'balik_nama',
            'new_taxpayer_id' => $this->oldWp->id, // Same WP!
            'legal_doc_type' => 'Akta Jual Beli (AJB)',
            'reason' => 'Balik nama',
        ]);
        $failRes->assertSessionHas('error');

        // 2. Propose with new taxpayer succeeds
        $res = $this->actingAs($this->kadus)->post(route('mutations.store'), [
            'nop' => $this->taxObject->nop,
            'mutation_type' => 'balik_nama',
            'new_taxpayer_id' => $this->newWp->id,
            'legal_doc_type' => 'Akta Jual Beli (AJB)',
            'legal_doc_number' => '045/AJB/2026',
            'reason' => 'Jual beli tanah darat antara Bpk Subur dan Ibu Ratna.',
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('sppt_mutations', [
            'tax_object_id' => $this->taxObject->id,
            'old_taxpayer_id' => $this->oldWp->id,
            'new_taxpayer_id' => $this->newWp->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_admin_cannot_approve_mutation()
    {
        $mutation = SpptMutation::create([
            'mutation_number' => SpptMutation::generateMutationNumber(),
            'tax_object_id' => $this->taxObject->id,
            'mutation_type' => 'balik_nama',
            'old_taxpayer_id' => $this->oldWp->id,
            'new_taxpayer_id' => $this->newWp->id,
            'requested_by_user_id' => $this->kadus->id,
            'legal_doc_type' => 'AJB',
            'reason' => 'Balik nama',
            'status' => 'pending',
        ]);

        $res = $this->actingAs($this->kadus)->post(route('mutations.approve', $mutation));
        $res->assertStatus(403);
    }

    public function test_admin_approves_mutation_which_unblocks_sppt_and_records_for_bapenda_reporting()
    {
        // Set object and SPPT to TO
        $this->taxObject->update(['status' => 'to']);
        $this->sppt->update(['is_to_blocked' => true]);

        $toCase = ToCase::create([
            'case_number' => ToCase::generateCaseNumber(),
            'tax_object_id' => $this->taxObject->id,
            'reported_by_user_id' => $this->kadus->id,
            'reason_type' => 'pemilik_tidak_dikenal',
            'description' => 'Pemilik awal tidak jelas.',
            'status' => 'investigating',
        ]);

        $mutation = SpptMutation::create([
            'mutation_number' => SpptMutation::generateMutationNumber(),
            'tax_object_id' => $this->taxObject->id,
            'to_case_id' => $toCase->id,
            'mutation_type' => 'balik_nama',
            'old_taxpayer_id' => $this->oldWp->id,
            'new_taxpayer_id' => $this->newWp->id,
            'requested_by_user_id' => $this->kolektor->id,
            'legal_doc_type' => 'AJB',
            'legal_doc_number' => '12/AJB/2026',
            'reason' => 'Sudah dibeli oleh Ibu Ratna',
            'status' => 'pending',
        ]);

        // Admin approves mutation
        $res = $this->actingAs($this->admin)->post(route('mutations.approve', $mutation));
        $res->assertSessionHas('success');

        // Check mutation is approved
        $this->assertTrue($mutation->fresh()->isApproved());
        $this->assertEquals($this->admin->id, $mutation->fresh()->approved_by_user_id);

        // Check ownership record retained for current tax year and object unblocked
        $this->assertEquals($this->oldWp->id, $this->taxObject->fresh()->taxpayer_id);
        $this->assertEquals('normal', $this->taxObject->fresh()->status);

        // Check SPPT unblocked and ready for payment
        $this->assertFalse((bool)$this->sppt->fresh()->is_to_blocked);
        $this->assertEquals('unpaid', $this->sppt->fresh()->status);

        // Check linked TO case is resolved
        $this->assertTrue($toCase->fresh()->isResolved());
        $this->assertNotNull($toCase->fresh()->resolved_at);

        // Kasir POS can now successfully pay this SPPT!
        $payRes = $this->actingAs($this->kadus)->post(route('payments.store'), [
            'sppt_id' => $this->sppt->id,
            'amount_principal' => 250000,
            'payment_date' => date('Y-m-d'),
        ]);

        $payRes->assertRedirect();
        $this->assertEquals(1, Payment::count());
        $this->assertEquals('paid', $this->sppt->fresh()->status);
    }
}
