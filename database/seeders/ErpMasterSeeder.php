<?php

namespace Database\Seeders;

use App\Models\BusinessPartner;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ErpMasterSeeder extends Seeder
{
    public function run(): void
    {
        $units = collect([
            ['code' => 'PCS', 'name' => 'Pieces', 'decimal_precision' => 0],
            ['code' => 'SET', 'name' => 'Set', 'decimal_precision' => 0],
            ['code' => 'MTR', 'name' => 'Meter', 'decimal_precision' => 2],
            ['code' => 'KG', 'name' => 'Kilogram', 'decimal_precision' => 3],
            ['code' => 'GR', 'name' => 'Gram', 'decimal_precision' => 2],
            ['code' => 'LTR', 'name' => 'Liter', 'decimal_precision' => 3],
        ])->mapWithKeys(function ($data) {
            $unit = Unit::withTrashed()->updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
            if ($unit->trashed()) {
                $unit->restore();
            }

            return [$unit->code => $unit];
        });

        Product::all()->each(function (Product $product) use ($units) {
            if ($unit = $units->get(strtoupper($product->uom))) {
                $product->update(['unit_id' => $unit->id]);
            }
        });

        $warehouse = Warehouse::withTrashed()->updateOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Gudang Utama', 'allow_negative_stock' => false, 'is_active' => true],
        );
        if ($warehouse->trashed()) {
            $warehouse->restore();
        }

        $supplier = BusinessPartner::withTrashed()->updateOrCreate(
            ['code' => 'SUP-DEMO'],
            ['name' => 'Vendor Demo', 'is_supplier' => true, 'is_customer' => false, 'is_active' => true],
        );
        if ($supplier->trashed()) {
            $supplier->restore();
        }

        $customer = BusinessPartner::withTrashed()->updateOrCreate(
            ['code' => 'CUST-WALKIN'],
            ['name' => 'Pelanggan Umum', 'is_supplier' => false, 'is_customer' => true, 'is_active' => true],
        );
        if ($customer->trashed()) {
            $customer->restore();
        }
    }
}
