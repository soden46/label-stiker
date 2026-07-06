<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->products() as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product + ['is_active' => true]
            );
        }
    }

    private function products(): array
    {
        return [
            ['sku' => '1000-P12-M8', 'name' => 'CON-STRAIGHT', 'description' => 'SIZE.3/8X1/4NPT', 'customer_part_no' => '40172501-0015', 'supplier_code' => 'BP-CN', 'barcode_value' => '1000-P12-M8', 'uom' => 'PCS'],
            ['sku' => '1000-E10-M8', 'name' => 'CON-ELBOW', 'description' => 'SIZE.5/16X1/4NPT', 'customer_part_no' => '40172501-0016', 'supplier_code' => 'BP-CN', 'barcode_value' => '1000-E10-M8', 'uom' => 'PCS'],
            ['sku' => '2000-T12-M8', 'name' => 'CON-TEE', 'description' => 'SIZE.3/8X1/4NPT', 'customer_part_no' => '40172501-0021', 'supplier_code' => 'WAF-ID', 'barcode_value' => '2000-T12-M8', 'uom' => 'PCS'],
            ['sku' => '3100-H06', 'name' => 'HOSE-NYLON', 'description' => 'SIZE.6MM BLACK', 'customer_part_no' => '40172502-0008', 'supplier_code' => 'HS-ID', 'barcode_value' => '3100-H06-BK', 'uom' => 'MTR'],
            ['sku' => '4100-V14', 'name' => 'CHECK-VALVE', 'description' => 'SIZE.1/4 NPT', 'customer_part_no' => '40172503-0012', 'supplier_code' => 'VL-CN', 'barcode_value' => '4100-V14', 'uom' => 'PCS'],
            ['sku' => '5100-F08', 'name' => 'FILTER-AIR', 'description' => 'PORT SIZE.1/4', 'customer_part_no' => '40172504-0003', 'supplier_code' => 'AF-ID', 'barcode_value' => '5100-F08', 'uom' => 'PCS'],
            ['sku' => '6100-R08', 'name' => 'REGULATOR-AIR', 'description' => 'PORT SIZE.1/4', 'customer_part_no' => '40172505-0007', 'supplier_code' => 'RG-JP', 'barcode_value' => '6100-R08', 'uom' => 'PCS'],
            ['sku' => '7200-G10', 'name' => 'PRESSURE-GAUGE', 'description' => 'RANGE.0-10BAR', 'customer_part_no' => '40172506-0011', 'supplier_code' => 'PG-ID', 'barcode_value' => '7200-G10', 'uom' => 'PCS'],
            ['sku' => '8200-S08', 'name' => 'SILENCER', 'description' => 'SIZE.1/4 NPT', 'customer_part_no' => '40172507-0004', 'supplier_code' => 'SL-CN', 'barcode_value' => '8200-S08', 'uom' => 'PCS'],
            ['sku' => '9100-C06', 'name' => 'TUBE-CUTTER', 'description' => 'FOR 3-12MM TUBE', 'customer_part_no' => '40172508-0002', 'supplier_code' => 'TC-JP', 'barcode_value' => '9100-C06', 'uom' => 'PCS'],
        ];
    }
}
