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

class DashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $kades;
    private User $admin;
    private User $kadus;

    protected function setUp(): void
    {
        parent::setUp();

        Village::create([
            'name' => 'Sukahaji',
            'district_name' => 'Sukahaji',
            'regency_name' => 'Majalengka',
            'target_amount' => 100000000,
            'active_tax_year' => 2026,
            'head_name' => 'Bapak Kepala Desa',
        ]);

        $this->kades = User::create([
            'name' => 'Kepala Desa Sukahaji',
            'username' => 'kades',
            'email' => 'kades@desa.id',
            'password' => bcrypt('password'),
            'role' => 'kades',
            'is_active' => true,
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
            'name' => 'Kadus Blok Timur',
            'username' => 'kadus1',
            'email' => 'kadus1@desa.id',
            'password' => bcrypt('password'),
            'role' => 'kadus',
            'dusun' => 'Dusun I',
            'is_active' => true,
        ]);

        $taxpayer = Taxpayer::create([
            'name' => 'Wajib Pajak Utama',
            'address' => 'RT 01 RW 01',
        ]);

        $taxObject = TaxObject::create([
            'nop' => '321205000100100777',
            'taxpayer_id' => $taxpayer->id,
            'address' => 'Blok Wage',
            'dusun' => 'Dusun I',
            'status' => 'normal',
        ]);

        $sppt = Sppt::create([
            'tax_object_id' => $taxObject->id,
            'tax_year' => 2026,
            'pbb_amount' => 500000,
            'total_bill' => 500000,
            'paid_amount' => 500000,
            'status' => 'paid',
            'due_date' => '2026-09-30',
        ]);

        Payment::create([
            'sppt_id' => $sppt->id,
            'cashier_user_id' => $this->kadus->id,
            'receipt_number' => Payment::generateReceiptNumber(2026),
            'verification_token' => Payment::generateVerificationToken(),
            'payment_date' => now(),
            'amount_principal' => 500000,
            'amount_fine' => 0,
            'total_amount' => 500000,
            'status' => 'valid',
        ]);
    }

    public function test_executive_dashboard_renders_with_metrics_for_kades_and_admin()
    {
        // 1. Kades accesses dashboard
        $resKades = $this->actingAs($this->kades)->get(route('dashboard'));
        $resKades->assertStatus(200);
        $resKades->assertSee('Pemerintah Desa Sukahaji');
        $resKades->assertSee('Ketetapan Baku DHKP');
        $resKades->assertSee('Realisasi Penerimaan');
        $resKades->assertSee('Dusun I');

        // 2. Admin accesses dashboard
        $resAdmin = $this->actingAs($this->admin)->get(route('dashboard'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Pemerintah Desa Sukahaji');
    }

    public function test_reports_hub_index_renders_all_four_report_links()
    {
        $res = $this->actingAs($this->kades)->get(route('reports.index'));
        $res->assertStatus(200);
        $res->assertSee('Pusat Laporan');
        $res->assertSee('Laporan Realisasi per Wilayah');
        $res->assertSee('Laporan Kuitansi Penerimaan Kasir');
        $res->assertSee('Laporan Arus Kas');
        $res->assertSee('Laporan Status TO');
    }

    public function test_realization_report_by_dusun_renders_and_exports_csv()
    {
        // HTML view
        $resHtml = $this->actingAs($this->kades)->get(route('reports.realization'));
        $resHtml->assertStatus(200);
        $resHtml->assertSee('LAPORAN REALISASI PENERIMAAN PBB-P2');
        $resHtml->assertSee('Dusun I');
        $resHtml->assertSee('500.000');

        // CSV export
        $resCsv = $this->actingAs($this->kades)->get(route('reports.realization', ['export' => 'excel']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('content-type'));
    }

    public function test_cashier_receipts_report_filters_and_exports_csv()
    {
        // HTML view
        $resHtml = $this->actingAs($this->kades)->get(route('reports.payments'));
        $resHtml->assertStatus(200);
        $resHtml->assertSee('LAPORAN TRANSAKSI PENERIMAAN KUITANSI PBB-P2');
        $resHtml->assertSee('Wajib Pajak Utama');

        // CSV export
        $resCsv = $this->actingAs($this->kades)->get(route('reports.payments', ['export' => 'excel']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('content-type'));
    }

    public function test_cash_flow_report_renders_and_exports_csv()
    {
        // HTML view
        $resHtml = $this->actingAs($this->kades)->get(route('reports.cash-flow'));
        $resHtml->assertStatus(200);
        $resHtml->assertSee('LAPORAN ARUS KAS');

        // CSV export
        $resCsv = $this->actingAs($this->kades)->get(route('reports.cash-flow', ['export' => 'excel']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('content-type'));
    }

    public function test_to_and_mutations_report_renders_and_exports_csv()
    {
        // HTML view
        $resHtml = $this->actingAs($this->kades)->get(route('reports.to-and-mutations'));
        $resHtml->assertStatus(200);
        $resHtml->assertSee('BUKU REGISTER MUTASI OBJEK PAJAK');

        // CSV export
        $resCsv = $this->actingAs($this->kades)->get(route('reports.to-and-mutations', ['export' => 'excel']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('content-type'));
    }
}
