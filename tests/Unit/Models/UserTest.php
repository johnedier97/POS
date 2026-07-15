<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CashRegisterSession;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_role(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(Role::class, $user->role);
    }

    public function test_user_has_many_sessions(): void
    {
        $user = User::factory()->create();
        CashRegisterSession::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->sessions);
    }

    public function test_role_is_eager_loaded(): void
    {
        $user = User::factory()->create();

        $userFromDb = User::find($user->id);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $role = $userFromDb->role;

        $queries = DB::getQueryLog();
        $roleQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'roles'));

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEmpty($roleQueries, 'role debería estar eager-loaded sin query extra');
    }
}
