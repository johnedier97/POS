<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_payment_method(): void
    {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(PaymentMethod::class, $payment->method);
        $this->assertInstanceOf(PaymentMethod::class, $payment->paymentMethod);
    }

    public function test_payment_belongs_to_sale(): void
    {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(Sale::class, $payment->sale);
    }
}
