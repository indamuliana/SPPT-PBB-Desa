<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideAndManualTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kadus;
    protected User $kolektor;
    protected User $bendahara;
    protected User $kades;
    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create([
            'name' => 'Sukahaji',
            'district' => 'Tanjungsari',
            'regency' => 'Kabupaten Sumedang',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->kadus = User::factory()->create(['role' => 'kadus', 'is_active' => true]);
        $this->kolektor = User::factory()->create(['role' => 'kolektor', 'is_active' => true]);
        $this->bendahara = User::factory()->create(['role' => 'bendahara', 'is_active' => true]);
        $this->kades = User::factory()->create(['role' => 'kades', 'is_active' => true]);
    }

    public function test_guest_is_redirected_from_guide_to_login(): void
    {
        $response = $this->get('/guide');
        $response->assertRedirect('/login');
    }

    public function test_all_authenticated_roles_can_view_guide_page(): void
    {
        foreach ([$this->admin, $this->kadus, $this->kolektor, $this->bendahara, $this->kades] as $user) {
            $response = $this->actingAs($user)->get('/guide');
            $response->assertStatus(200);
            $response->assertSee('Buku Panduan & Alur Kerja SADAYA PBB', false);
            $response->assertSee('Admin Desa');
            $response->assertSee('Kadus / Kasir');
            $response->assertSee('Kolektor Desa');
            $response->assertSee('Bendahara');
            $response->assertSee('Kepala Desa');
            $response->assertSee('Unduh Panduan PDF Resmi');
        }
    }

    public function test_user_can_download_official_guide_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get('/guide/download-pdf');

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Buku_Panduan_SADAYA_PBB_Sukahaji_2026.pdf', $response->headers->get('Content-Disposition', ''));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_guide_link_appears_in_sidebar(): void
    {
        $response = $this->actingAs($this->kadus)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee(route('guide.index'));
        $response->assertSee('Panduan Aplikasi');
    }
}
