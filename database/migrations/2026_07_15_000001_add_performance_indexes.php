<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['type', 'created_at'], 'sales_type_created_at_index');
        });

        Schema::table('cash_register_sessions', function (Blueprint $table) {
            $table->index('status', 'cash_register_sessions_status_index');
        });

        $duplicates = DB::table('inventories')
            ->select('product_id', 'branch_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('product_id', 'branch_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('inventories')
                ->where('product_id', $dup->product_id)
                ->where('branch_id', $dup->branch_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('inventories', function (Blueprint $table) {
            $table->unique(['product_id', 'branch_id'], 'inventories_product_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('inventories_product_branch_unique');
        });

        Schema::table('cash_register_sessions', function (Blueprint $table) {
            $table->dropIndex('cash_register_sessions_status_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_type_created_at_index');
        });
    }
};
