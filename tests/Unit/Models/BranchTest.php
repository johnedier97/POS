<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\CashRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_has_many_cash_registers(): void
    {
        $branch = Branch::factory()->create();
        CashRegister::factory()->count(3)->create(['branch_id' => $branch->id]);

        $this->assertCount(3, $branch->cashRegisters);
        $this->assertInstanceOf(CashRegister::class, $branch->cashRegisters->first());
    }
}
