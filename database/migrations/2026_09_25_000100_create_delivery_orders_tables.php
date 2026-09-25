<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number', 60)->unique();
            $table->date('delivery_date')->index();
            $table->foreignId('to_partner_id')->constrained('business_partners')->restrictOnDelete();
            $table->foreignId('ship_to_partner_id')->constrained('business_partners')->restrictOnDelete();
            $table->string('to_company', 160);
            $table->string('to_project_site', 160)->nullable();
            $table->text('to_address');
            $table->string('ship_to_company', 160);
            $table->string('ship_to_project_site', 160)->nullable();
            $table->text('ship_to_address');
            $table->string('ship_to_phone', 40)->nullable();
            $table->string('purchase_order_no', 100)->nullable()->index();
            $table->string('fob', 100)->nullable();
            $table->string('packages', 100)->nullable();
            $table->string('ship_via', 100)->nullable();
            $table->string('shipment', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['delivery_date', 'to_partner_id']);
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->unsignedInteger('line_number');
            $table->string('item_name', 200);
            $table->string('waf_part_no', 150);
            $table->string('customer_part_no', 150)->nullable();
            $table->string('catalog_code', 150)->nullable();
            $table->decimal('quantity', 19, 4);
            $table->string('unit', 20);
            $table->decimal('weight', 19, 4)->nullable();
            $table->timestamps();

            $table->unique(['delivery_order_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
};
