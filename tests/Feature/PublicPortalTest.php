<?php

namespace Tests\Feature;

use App\Models\Dusun;
use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Village $village;
    protected Dusun $dusun;
    protected Taxpayer $taxpayer;
    protected TaxObject $taxObject;
    protected Sppt $sppt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Desa Sukahaji',
            'active_tax_year' => 2026,
            'target_amount' => 150000000.00,
        ]);

        $this->dusun = Dusun::create([
            'name' => 'Dusun Sukahaji I',
            'code' => 'DUS-01',
        ]);

        $kadus = User::factory()->create([
            'username' => 'kadus_portal',
            'name' => 'Bapak Kadus 1',
            'role' => 'kadus',
            'is_active' => true,
        ]);

        $this->dusun->kadus()->attach($kadus->id);

        $this->taxpayer = Taxpayer::create([
            'name' => 'Ujang Suherman',
            'nik' => '3211050101900001',
            'address' => 'Jl. Tanjungsari No. 12',
            'dusun' => 'Dusun Sukahaji I',
        ]);

        $this->taxObject = TaxObject::create([
            'nop' => '321105000100100055',
            'taxpayer_id' => $this->taxpayer->id,
            'dusun_id' => $this->dusun->id,
            'dusun' => 'Dusun Sukahaji I',
            'address' => 'Blok Pasir Gede RT 01/02',
            'status' => 'normal',
        ]);

        $this->sppt = Sppt::create([
            'tax_object_id' => $this->taxObject->id,
            'tax_year' => 2026,
            'pbb_terutang' => 125000,
            'total_bill' => 125000,
            'paid_amount' => 50000,
            'remaining_bill' => 75000,
            'status' => 'partial',
            'due_date' => '2026-09-30',
        ]);
    }

    public function test_public_welcome_portal_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SADAYA PBB');
        $response->assertSee('Desa Sukahaji');
        $response->assertSee('kantor-desa-sukahaji.png');
        $response->assertSee('Capaian Penerimaan PBB');
        $response->assertSee('Dusun Sukahaji I');
        $response->assertSee('Bapak Kadus 1');
        $response->assertSee('By Himagrib');
    }

    public function test_public_check_nop_returns_valid_data(): void
    {
        $response = $this->getJson('/api/public/check-nop?nop=321105000100100055');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'nop' => '321105000100100055',
            'taxpayer_name' => 'Ujang Suherman',
            'dusun' => 'Dusun Sukahaji I',
            'kadus_name' => 'Bapak Kadus 1',
            'tax_year' => 2026,
            'total_bill' => 125000,
            'paid_amount' => 50000,
            'remaining_bill' => 75000,
            'status' => 'partial',
        ]);
    }

    public function test_public_check_nop_handles_formatted_nop(): void
    {
        $response = $this->getJson('/api/public/check-nop?nop=' . urlencode($this->taxObject->formatted_nop));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'taxpayer_name' => 'Ujang Suherman',
        ]);
    }

    public function test_public_check_nop_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/public/check-nop?nop=999999999999999999');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_public_check_nop_too_short_returns_422(): void
    {
        $response = $this->getJson('/api/public/check-nop?nop=123');
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
