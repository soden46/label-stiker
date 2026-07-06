<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_prints', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purchase_order_no')->index();
            $table->string('customer_part_no');
            $table->unsignedInteger('quantity');
            $table->string('uom', 20);
            $table->string('barcode_value');
            $table->json('product_snapshot');
            $table->timestamp('printed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['created_at', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_prints');
    }
};
