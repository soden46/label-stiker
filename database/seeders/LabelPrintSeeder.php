<?php

namespace Database\Seeders;

use App\Models\LabelPrint;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LabelPrintSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@labelin.test')->firstOrFail();
        $products = Product::where('is_active', true)->orderBy('id')->get();

        LabelPrint::where('purchase_order_no', 'like', 'DEMO-%')->delete();

        foreach (range(0, 23) as $monthOffset) {
            foreach (range(1, 3) as $sequence) {
                $index = ($monthOffset * 3) + $sequence;
                $product = $products[($index - 1) % $products->count()];
                $createdAt = now()
                    ->subMonthsNoOverflow($monthOffset)
                    ->startOfMonth()
                    ->addDays(min(($sequence * 7) + ($monthOffset % 4), 25))
                    ->setTime(8 + ($index % 8), ($index * 7) % 60);

                LabelPrint::create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $product->id,
                    'created_by' => $admin->id,
                    'purchase_order_no' => sprintf('DEMO-%s-%02d', $createdAt->format('Ym'), $sequence),
                    'delivery_note_no' => sprintf('SJ-DEMO-%s-%02d', $createdAt->format('Ym'), $sequence),
                    'customer_part_no' => $product->customer_part_no,
                    'quantity' => [25, 50, 100, 250, 500][$index % 5],
                    'inventory_stock' => 0,
                    'uom' => $product->uom,
                    'sender_address' => 'PT WAF Indonesia, Kawasan Industri Demo, Bekasi',
                    'recipient_address' => 'Customer Demo, Gudang Penerima, Jakarta',
                    'barcode_value' => $product->barcode_value,
                    'product_snapshot' => $product->only([
                        'sku', 'name', 'description', 'customer_part_no',
                        'supplier_code', 'barcode_value', 'uom',
                    ]),
                    'printed_at' => $index % 4 === 0 ? null : $createdAt->copy()->addMinutes(4),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
