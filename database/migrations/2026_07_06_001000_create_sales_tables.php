<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('number', 60)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('business_partners')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->string('channel', 30)->default('pos')->index();
            $table->char('currency', 3)->default('IDR');
            $table->decimal('subtotal', 19, 4)->default(0);
            $table->decimal('discount_total', 19, 4)->default(0);
            $table->decimal('tax_total', 19, 4)->default(0);
            $table->decimal('grand_total', 19, 4)->default(0);
            $table->decimal('cost_of_goods_sold', 19, 4)->default(0);
            $table->decimal('gross_profit', 19, 4)->default(0);
            $table->decimal('paid_amount', 19, 4)->default(0);
            $table->decimal('change_amount', 19, 4)->default(0);
            $table->timestamp('sold_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_price', 19, 4);
            $table->decimal('discount', 19, 4)->default(0);
            $table->decimal('tax', 19, 4)->default(0);
            $table->decimal('line_total', 19, 4);
            $table->decimal('unit_cost', 19, 4)->default(0);
            $table->decimal('cost_total', 19, 4)->default(0);
            $table->decimal('gross_profit', 19, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30)->index();
            $table->decimal('amount', 19, 4);
            $table->string('reference_number', 100)->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
