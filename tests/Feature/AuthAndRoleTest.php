<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
