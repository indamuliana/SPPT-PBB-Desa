<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Village::create([
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
        ]);
    }

    public function test_public_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Desa Sukahaji');
        $response->assertSee('175.000.000');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SADAYA PBB');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'kadus1',
            'password' => bcrypt('password'),
            'role' => 'kadus',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'kadus1',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'kadus1',
            'role' => 'kadus',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Kadus / Kasir');
    }

    public function test_non_admin_cannot_access_village_settings(): void
    {
        $kadus = User::factory()->create([
            'username' => 'kadus1',
            'role' => 'kadus',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kadus)->get('/settings/village');
        $response->assertStatus(403);
    }

    public function test_admin_can_update_village_settings(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put('/settings/village', [
            'name' => 'Desa Sukahaji Maju',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'postal_code' => '45258',
            'address' => 'Jl. Balai Desa No. 1',
            'phone' => '0234123456',
            'email' => 'admin@sukahaji.id',
            'kades_name' => 'H. Mulyadi',
            'kades_nip' => '197508152006041008',
            'active_tax_year' => 2026,
            'target_amount' => 200000000,
            'tax_rate_percent' => 0.100,
        ]);

        $response->assertRedirect(route('settings.village'));
        $this->assertDatabaseHas('villages', [
            'name' => 'Desa Sukahaji Maju',
            'target_amount' => 200000000,
            'tax_rate_percent' => 0.100,
        ]);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'update',
        ]);
    }

    public function test_collector_can_upload_and_remove_logos_and_hero_image(): void
    {
        $kolektor = User::factory()->create([
            'username' => 'kolektor_desa',
            'role' => 'kolektor',
            'is_active' => true,
        ]);

        $logoDesa = UploadedFile::fake()->image('logo_desa.png', 100, 100);
        $logoKab = UploadedFile::fake()->image('logo_kabupaten.png', 100, 100);
        $heroImage = UploadedFile::fake()->image('foto_desa.jpg', 1200, 800);

        $response = $this->actingAs($kolektor)->put('/settings/village', [
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
            'tax_rate_percent' => 0.100,
            'logo_desa' => $logoDesa,
            'logo_kabupaten' => $logoKab,
            'hero_image' => $heroImage,
        ]);

        $response->assertRedirect(route('settings.village'));
        $village = Village::current()->fresh();

        $this->assertNotNull($village->logo_path);
        $this->assertNotNull($village->regency_logo_path);
        $this->assertNotNull($village->hero_image_path);
        $this->assertFileExists(public_path($village->logo_path));
        $this->assertFileExists(public_path($village->regency_logo_path));
        $this->assertFileExists(public_path($village->hero_image_path));

        // Test removing the uploaded files
        $removeResponse = $this->actingAs($kolektor)->put('/settings/village', [
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'active_tax_year' => 2026,
            'target_amount' => 175000000.00,
            'tax_rate_percent' => 0.100,
            'remove_logo_desa' => 1,
            'remove_logo_kabupaten' => 1,
            'remove_hero_image' => 1,
        ]);

        $removeResponse->assertRedirect(route('settings.village'));
        $villageFresh = $village->fresh();
        $this->assertNull($villageFresh->logo_path);
        $this->assertNull($villageFresh->regency_logo_path);
        $this->assertNull($villageFresh->hero_image_path);
    }
}
