<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_detail_belongs_to_product(): void
    {
        $detail = SaleDetail::factory()->create();
        $this->assertInstanceOf(Product::class, $detail->product);
    }

    public function test_sale_detail_belongs_to_sale(): void
    {
        $detail = SaleDetail::factory()->create();
        $this->assertInstanceOf(Sale::class, $detail->sale);
    }

    public function test_subtotal_is_calculated_correctly(): void
    {
        $detail = SaleDetail::factory()->create([
            'quantity' => 3,
            'price' => 10.00,
        ]);

        $expected = 3 * 10.00;
        $this->assertEquals($expected, $detail->subtotal);
    }
}
