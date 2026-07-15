<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRegisterSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_open_returns_true_for_open_session(): void
    {
        $session = CashRegisterSession::factory()->open()->create();
        $this->assertTrue($session->isOpen());
    }

    public function test_is_open_returns_false_for_closed_session(): void
    {
        $session = CashRegisterSession::factory()->closed()->create();
        $this->assertFalse($session->isOpen());
    }

    public function test_session_belongs_to_cash_register(): void
    {
        $session = CashRegisterSession::factory()->create();
        $this->assertInstanceOf(CashRegister::class, $session->cashRegister);
    }

    public function test_session_belongs_to_user(): void
    {
        $session = CashRegisterSession::factory()->create();
        $this->assertInstanceOf(User::class, $session->user);
    }

    public function test_decimal_casts_work(): void
    {
        $session = CashRegisterSession::factory()->create(['initial_balance' => 100.50]);
        $this->assertEquals(100.50, (float) $session->initial_balance);
    }
}
