<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_register_belongs_to_branch(): void
    {
        $register = CashRegister::factory()->create();
        $this->assertInstanceOf(Branch::class, $register->branch);
    }

    public function test_cash_register_has_many_sessions(): void
    {
        $register = CashRegister::factory()->create();
        CashRegisterSession::factory()->count(2)->create(['cash_register_id' => $register->id]);

        $this->assertCount(2, $register->sessions);
    }

    public function test_current_session_returns_open_session(): void
    {
        $register = CashRegister::factory()->create();
        $openSession = CashRegisterSession::factory()->open()->create(['cash_register_id' => $register->id]);
        CashRegisterSession::factory()->closed()->create(['cash_register_id' => $register->id]);

        $current = $register->currentSession;
        $this->assertNotNull($current);
        $this->assertEquals($openSession->id, $current->id);
    }

    public function test_current_session_returns_null_when_no_open_session(): void
    {
        $register = CashRegister::factory()->create();
        CashRegisterSession::factory()->closed()->create(['cash_register_id' => $register->id]);

        $this->assertNull($register->currentSession);
    }
}
