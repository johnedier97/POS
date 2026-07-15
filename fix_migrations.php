<?php

$dir = __DIR__.'/database/migrations';
$files = scandir($dir);

$schemas = [
    'roles' => "\$table->string('name')->unique();\n",
    'branches' => "\$table->string('name');\n\$table->string('address')->nullable();\n",
    'cash_registers' => "\$table->foreignId('branch_id')->constrained()->onDelete('cascade');\n\$table->string('name');\n\$table->boolean('is_active')->default(true);\n",
    'cash_register_sessions' => "\$table->foreignId('cash_register_id')->constrained()->onDelete('cascade');\n\$table->foreignId('user_id')->constrained('users')->onDelete('cascade');\n\$table->dateTime('opened_at')->nullable();\n\$table->dateTime('closed_at')->nullable();\n\$table->decimal('initial_balance', 15, 2)->default(0);\n\$table->decimal('final_calculated_balance', 15, 2)->nullable();\n\$table->decimal('final_reported_balance', 15, 2)->nullable();\n\$table->enum('status', ['open', 'closed'])->default('open');\n",
    'unit_of_measures' => "\$table->string('name');\n\$table->string('abbreviation', 10);\n",
    'products' => "\$table->string('name');\n\$table->text('description')->nullable();\n\$table->string('image_path')->nullable();\n\$table->decimal('cost', 15, 2)->default(0);\n\$table->decimal('price', 15, 2)->default(0);\n\$table->foreignId('unit_of_measure_id')->nullable()->constrained()->onDelete('set null');\n\$table->boolean('is_composite')->default(false);\n",
    'product_components' => "\$table->foreignId('parent_product_id')->constrained('products')->onDelete('cascade');\n\$table->foreignId('child_product_id')->constrained('products')->onDelete('cascade');\n\$table->decimal('quantity', 10, 4);\n",
    'inventories' => "\$table->foreignId('product_id')->constrained()->onDelete('cascade');\n\$table->foreignId('branch_id')->constrained()->onDelete('cascade');\n\$table->decimal('stock', 15, 4)->default(0);\n",
    'suppliers' => "\$table->string('name');\n\$table->string('contact_name')->nullable();\n\$table->string('email')->nullable();\n\$table->string('phone')->nullable();\n",
    'purchase_orders' => "\$table->foreignId('supplier_id')->constrained()->onDelete('cascade');\n\$table->foreignId('branch_id')->constrained()->onDelete('cascade');\n\$table->dateTime('date');\n\$table->enum('status', ['pending', 'received'])->default('pending');\n\$table->decimal('total', 15, 2)->default(0);\n",
    'purchase_order_details' => "\$table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');\n\$table->foreignId('product_id')->constrained()->onDelete('cascade');\n\$table->decimal('quantity', 10, 4);\n\$table->decimal('unit_cost', 15, 2)->default(0);\n\$table->decimal('total_cost', 15, 2)->default(0);\n",
    'customers' => "\$table->string('name');\n\$table->string('document')->nullable();\n\$table->string('email')->nullable();\n\$table->string('phone')->nullable();\n",
    'sales' => "\$table->foreignId('session_id')->nullable()->constrained('cash_register_sessions')->onDelete('set null');\n\$table->foreignId('user_id')->constrained('users')->onDelete('cascade');\n\$table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');\n\$table->enum('type', ['sale', 'waste'])->default('sale');\n\$table->decimal('total', 15, 2)->default(0);\n\$table->boolean('is_electronic_invoiced')->default(false);\n",
    'sale_details' => "\$table->foreignId('sale_id')->constrained()->onDelete('cascade');\n\$table->foreignId('product_id')->constrained()->onDelete('cascade');\n\$table->decimal('quantity', 10, 4);\n\$table->decimal('price', 15, 2)->default(0);\n\$table->decimal('cost', 15, 2)->default(0);\n\$table->decimal('subtotal', 15, 2)->default(0);\n",
    'payment_methods' => "\$table->string('name');\n\$table->boolean('is_active')->default(true);\n",
    'payments' => "\$table->foreignId('sale_id')->constrained()->onDelete('cascade');\n\$table->foreignId('payment_method_id')->constrained()->onDelete('cascade');\n\$table->decimal('amount', 15, 2);\n",
    'settings' => "\$table->string('key')->unique();\n\$table->text('value')->nullable();\n",
];

$template = <<<"EOT"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('__TABLE_NAME__', function (Blueprint \$table) {
            \$table->id();
            __SCHEMA__
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('__TABLE_NAME__');
    }
};
EOT;

foreach ($files as $file) {
    if (strpos($file, 'create_users_table') !== false) {
        $usersPath = $dir.'/'.$file;
        $content = file_get_contents($usersPath);
        if (strpos($content, 'role_id') === false) {
            $content = str_replace(
                "\$table->string('password');",
                "\$table->string('password');\n            \$table->unsignedBigInteger('role_id')->nullable();",
                $content
            );
            file_put_contents($usersPath, $content);
            echo "Updated users table\n";
        }
    }
}

foreach ($schemas as $tableName => $schema) {
    foreach ($files as $file) {
        if (strpos($file, 'create_'.$tableName.'_table') !== false) {
            $path = $dir.'/'.$file;
            $indentedSchema = str_replace("\n", "\n            ", rtrim($schema));
            $content = str_replace(['__TABLE_NAME__', '__SCHEMA__'], [$tableName, $indentedSchema], $template);
            file_put_contents($path, $content);
            echo "Fixed {$file}\n";
            break;
        }
    }
}
