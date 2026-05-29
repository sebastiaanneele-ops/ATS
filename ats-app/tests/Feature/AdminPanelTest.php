<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertSuccessful();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get('/admin')
            ->assertSuccessful();
    }
}
