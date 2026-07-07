<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 80);
            $table->unsignedTinyInteger('decimal_precision')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->text('address')->nullable();
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_partners', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 160);
            $table->boolean('is_supplier')->default(false)->index();
            $table->boolean('is_customer')->default(false)->index();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 60)->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50);
            $table->string('period', 20);
            $table->string('prefix', 30);
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(5);
            $table->timestamps();
            $table->unique(['key', 'period']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('uom')->constrained('units')->nullOnDelete();
            $table->string('item_type', 30)->default('trading')->after('unit_id')->index();
            $table->boolean('track_inventory')->default(true)->after('item_type')->index();
            $table->string('cost_method', 30)->default('weighted_average')->after('track_inventory');
            $table->decimal('standard_cost', 19, 4)->default(0)->after('cost_method');
            $table->decimal('selling_price', 19, 4)->default(0)->after('standard_cost');
            $table->decimal('minimum_stock', 19, 4)->default(0)->after('selling_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn(['item_type', 'track_inventory', 'cost_method', 'standard_cost', 'selling_price', 'minimum_stock']);
        });
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('business_partners');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('units');
    }
};
