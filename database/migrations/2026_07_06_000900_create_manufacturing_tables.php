<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('version', 30)->default('1');
            $table->decimal('output_quantity', 19, 4)->default(1);
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'version']);
        });

        Schema::create('bill_of_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('quantity', 19, 4);
            $table->decimal('scrap_percentage', 8, 4)->default(0);
            $table->timestamps();
            $table->unique(['bill_of_material_id', 'material_product_id'], 'bom_material_unique');
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 60)->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('bill_of_material_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('material_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('output_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->decimal('planned_quantity', 19, 4);
            $table->decimal('completed_quantity', 19, 4)->default(0);
            $table->timestamp('planned_start_at')->nullable();
            $table->timestamp('planned_end_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('material_cost', 19, 4)->default(0);
            $table->decimal('overhead_cost', 19, 4)->default(0);
            $table->decimal('total_cost', 19, 4)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('planned_quantity', 19, 4);
            $table->decimal('issued_quantity', 19, 4)->default(0);
            $table->decimal('unit_cost', 19, 4)->default(0);
            $table->decimal('total_cost', 19, 4)->default(0);
            $table->timestamps();
            $table->unique(['production_order_id', 'product_id'], 'production_material_unique');
        });

        Schema::create('production_order_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_cost', 19, 4);
            $table->timestamp('produced_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_outputs');
        Schema::dropIfExists('production_order_materials');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('bill_of_material_items');
        Schema::dropIfExists('bill_of_materials');
    }
};
