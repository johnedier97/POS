<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $salesRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRole = Role::factory()->admin()->create();
        $this->salesRole = Role::factory()->sales()->create();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
    }

    public function test_sales_user_cannot_access_admin_routes(): void
    {
        $salesUser = User::factory()->create(['role_id' => $this->salesRole->id]);

        $response = $this->actingAs($salesUser)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($salesUser)->get('/products');
        $response->assertStatus(403);

        $response = $this->actingAs($salesUser)->get('/suppliers');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_without_role_gets_403(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);
    }
}
