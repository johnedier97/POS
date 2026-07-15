<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_role(): void
    {
        $role = Role::factory()->create();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_admin_state_creates_admin_role(): void
    {
        $role = Role::factory()->admin()->create();
        $this->assertEquals('admin', $role->name);
    }

    public function test_sales_state_creates_sales_role(): void
    {
        $role = Role::factory()->sales()->create();
        $this->assertEquals('sales', $role->name);
    }
}
