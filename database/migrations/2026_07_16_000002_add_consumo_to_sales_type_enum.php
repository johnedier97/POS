<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('sales_type_created_at_index');
                $table->dropColumn('type');
            });
            Schema::table('sales', function (Blueprint $table) {
                $table->string('type')->default('sale')->after('customer_id');
                $table->index(['type', 'created_at'], 'sales_type_created_at_index');
            });
        } else {
            DB::statement("ALTER TABLE sales MODIFY COLUMN type ENUM('sale', 'waste', 'consumo') NOT NULL DEFAULT 'sale'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('sales_type_created_at_index');
                $table->dropColumn('type');
            });
            Schema::table('sales', function (Blueprint $table) {
                $table->string('type')->default('sale')->after('customer_id');
                $table->index(['type', 'created_at'], 'sales_type_created_at_index');
            });
        } else {
            DB::statement("ALTER TABLE sales MODIFY COLUMN type ENUM('sale', 'waste') NOT NULL DEFAULT 'sale'");
        }
    }
};
