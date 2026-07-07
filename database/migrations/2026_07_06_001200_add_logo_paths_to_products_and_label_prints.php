<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('barcode_value');
        });
        Schema::table('label_prints', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('barcode_value');
        });
    }

    public function down(): void
    {
        Schema::table('label_prints', fn (Blueprint $table) => $table->dropColumn('logo_path'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('logo_path'));
    }
};
