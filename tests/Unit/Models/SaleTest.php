<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_has_many_details(): void
    {
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(3)->create(['sale_id' => $sale->id]);

        $this->assertCount(3, $sale->details);
        $this->assertInstanceOf(SaleDetail::class, $sale->details->first());
    }

    public function test_sale_has_many_payments(): void
    {
        $sale = Sale::factory()->create();
        Payment::factory()->count(2)->create(['sale_id' => $sale->id]);

        $this->assertCount(2, $sale->payments);
        $this->assertInstanceOf(Payment::class, $sale->payments->first());
    }

    public function test_sale_belongs_to_session(): void
    {
        $sale = Sale::factory()->create();
        $this->assertNotNull($sale->session);
    }

    public function test_sale_belongs_to_user(): void
    {
        $sale = Sale::factory()->create();
        $this->assertNotNull($sale->user);
    }

    public function test_sale_can_belong_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $sale = Sale::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $sale->customer);
    }

    public function test_waste_state_sets_type(): void
    {
        $sale = Sale::factory()->waste()->create();
        $this->assertEquals('waste', $sale->type);
    }

    public function test_invoiced_state(): void
    {
        $sale = Sale::factory()->invoiced()->create();
        $this->assertTrue($sale->is_electronic_invoiced);
    }
}
