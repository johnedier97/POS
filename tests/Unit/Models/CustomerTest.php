<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_customer(): void
    {
        $customer = Customer::factory()->create();
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_fillable_attributes_are_set(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Juan Pérez',
            'document' => '12345678',
            'email' => 'juan@example.com',
            'phone' => '555-0100',
        ]);

        $this->assertEquals('Juan Pérez', $customer->name);
        $this->assertEquals('12345678', $customer->document);
        $this->assertEquals('juan@example.com', $customer->email);
        $this->assertEquals('555-0100', $customer->phone);
    }
}
