<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 19, 4)->default(0);
            $table->decimal('average_cost', 19, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('movement_type', 40)->index();
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_cost', 19, 4)->default(0);
            $table->decimal('total_cost', 19, 4)->default(0);
            $table->decimal('balance_after', 19, 4);
            $table->nullableMorphs('reference');
            $table->timestamp('occurred_at')->index();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'warehouse_id', 'occurred_at'], 'stock_product_warehouse_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_balances');
    }
};
