<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('label_prints', function (Blueprint $table) {
            $table->string('delivery_note_no')->nullable()->after('purchase_order_no');
            $table->decimal('inventory_stock', 19, 4)->default(0)->after('quantity');
            $table->text('sender_address')->nullable()->after('uom');
            $table->text('recipient_address')->nullable()->after('sender_address');
        });
    }

    public function down(): void
    {
        Schema::table('label_prints', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_note_no',
                'inventory_stock',
                'sender_address',
                'recipient_address',
            ]);
        });
    }
};
