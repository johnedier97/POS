<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Jobs\SendCashRegisterClosedMail;
use App\Models\CashRegisterSession;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CashRegisterSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private PaymentMethod $cashMethod;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();

        $this->user = User::factory()->create();
        $this->cashMethod = PaymentMethod::factory()->create(['name' => 'Efectivo', 'is_active' => true]);
    }

    public function test_open_session(): void
    {
        $register = \App\Models\CashRegister::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user)->post('/shift/open', [
            'cash_register_id' => $register->id,
            'initial_balance' => 200.00,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('cash_register_sessions', [
            'cash_register_id' => $register->id,
            'user_id' => $this->user->id,
            'initial_balance' => 200.00,
            'status' => 'open',
        ]);
    }

    public function test_close_session_with_exact_balance(): void
    {
        $session = CashRegisterSession::factory()->open()->create([
            'user_id' => $this->user->id,
            'initial_balance' => 100.00,
        ]);

        $sale = Sale::factory()->create([
            'session_id' => $session->id,
            'user_id' => $this->user->id,
            'type' => 'sale',
            'total' => 50.00,
        ]);
        Payment::factory()->create([
            'sale_id' => $sale->id,
            'payment_method_id' => $this->cashMethod->id,
            'amount' => 50.00,
        ]);

        $response = $this->actingAs($this->user)->put("/shift/close/{$session->id}", [
            'final_reported_balance' => 150.00,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('cash_register_sessions', [
            'id' => $session->id,
            'status' => 'closed',
            'final_calculated_balance' => 150.00,
            'final_reported_balance' => 150.00,
        ]);

        Bus::assertDispatched(SendCashRegisterClosedMail::class);
    }

    public function test_close_session_with_mismatch_fails(): void
    {
        $session = CashRegisterSession::factory()->open()->create([
            'user_id' => $this->user->id,
            'initial_balance' => 100.00,
        ]);

        $sale = Sale::factory()->create([
            'session_id' => $session->id,
            'type' => 'sale',
            'total' => 50.00,
        ]);
        Payment::factory()->create([
            'sale_id' => $sale->id,
            'payment_method_id' => $this->cashMethod->id,
            'amount' => 50.00,
        ]);

        $response = $this->actingAs($this->user)->put("/shift/close/{$session->id}", [
            'final_reported_balance' => 160.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cash_register_sessions', [
            'id' => $session->id,
            'status' => 'open',
        ]);

        Bus::assertNotDispatched(SendCashRegisterClosedMail::class);
    }

    public function test_cannot_open_second_session(): void
    {
        CashRegisterSession::factory()->open()->create(['user_id' => $this->user->id]);
        $register = \App\Models\CashRegister::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user)->get('/shift/open');
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('info');
    }

    public function test_cannot_close_already_closed_session(): void
    {
        $session = CashRegisterSession::factory()->closed()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/shift/close/{$session->id}");
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }
}
