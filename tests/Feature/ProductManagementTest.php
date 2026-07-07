<?php

namespace Tests\Feature;

use App\Models\LabelPrint;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_without_existing_barcode_uses_sku_as_internal_barcode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('products.store'), [
            'sku' => 'SKU-TANPA-BARCODE',
            'name' => 'Produk Tanpa Barcode',
            'barcode_value' => '',
            'uom' => 'PCS',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-TANPA-BARCODE',
            'barcode_value' => 'SKU-TANPA-BARCODE',
        ]);
    }

    public function test_admin_can_import_the_client_excel_format(): void
    {
        $user = User::factory()->create();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['DESCRIPTION', 'REMARKS', 'CUSTOMER PART', 'P.O NUMBER', 'QTY', 'UOM', 'CODE', 'BARCODE', 'BARCODE'],
            ['CON-STRAIGHT', 'SIZE.3/8X1/4NPT', '40172501-0015', '1011873938', 100, 'PCS', 'BP-CN', 'LINK CATALOG PT.WAF', 'PARTS NUMBER'],
        ], null, 'B3');

        $path = tempnam(sys_get_temp_dir(), 'product-import-');
        (new Xlsx($spreadsheet))->save($path);

        try {
            $response = $this->actingAs($user)->post(route('products.import'), [
                'product_file' => new UploadedFile(
                    $path,
                    'LINK BARCODE DARI DATABASE.xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    null,
                    true,
                ),
            ]);

            $response->assertRedirect()->assertSessionHas('import_result.created', 1);
            $this->assertDatabaseHas('products', [
                'sku' => '40172501-0015',
                'name' => 'CON-STRAIGHT',
                'description' => 'SIZE.3/8X1/4NPT',
                'customer_part_no' => '40172501-0015',
                'supplier_code' => 'BP-CN',
                'barcode_value' => '40172501-0015',
                'uom' => 'PCS',
            ]);
        } finally {
            @unlink($path);
        }
    }

    public function test_admin_can_edit_and_safely_delete_a_product(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'sku' => 'EDIT-001',
            'name' => 'OLD NAME',
            'barcode_value' => 'EDIT-001',
            'uom' => 'PCS',
        ]);

        $this->actingAs($user)->put(route('products.update', $product), [
            'sku' => 'EDIT-002',
            'name' => 'NEW NAME',
            'barcode_value' => '',
            'uom' => 'SET',
            'is_active' => '1',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'EDIT-002',
            'name' => 'NEW NAME',
            'barcode_value' => 'EDIT-002',
            'uom' => 'SET',
        ]);

        $this->actingAs($user)->post(route('labels.store'), [
            'product_id' => $product->id,
            'purchase_order_no' => 'PO-HISTORY-001',
            'customer_part_no' => 'CUST-HISTORY-001',
            'quantity' => 10,
            'uom' => 'SET',
        ]);

        $this->actingAs($user)->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame($product->id, LabelPrint::firstOrFail()->product->id);
    }

    public function test_product_logo_is_optional_and_snapshotted_when_generating_label(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('products.store'), [
            'sku' => 'LOGO-001', 'name' => 'Part Logo', 'uom' => 'PCS',
            'logo' => UploadedFile::fake()->image('part-logo.png', 600, 450),
        ])->assertRedirect();
        $product = Product::where('sku', 'LOGO-001')->firstOrFail();
        Storage::disk('public')->assertExists($product->logo_path);

        $this->actingAs($user)->post(route('labels.store'), [
            'product_id' => $product->id, 'purchase_order_no' => 'PO-LOGO-1',
            'customer_part_no' => 'CUST-LOGO-1', 'quantity' => 10, 'uom' => 'PCS',
        ])->assertRedirect();
        $label = LabelPrint::latest('id')->firstOrFail();
        $this->assertNotSame($product->logo_path, $label->logo_path);
        Storage::disk('public')->assertExists($label->logo_path);

        $this->actingAs($user)->post(route('labels.store'), [
            'product_id' => $product->id, 'purchase_order_no' => 'PO-LOGO-2',
            'customer_part_no' => 'CUST-LOGO-2', 'quantity' => 10, 'uom' => 'PCS',
            'logo' => UploadedFile::fake()->image('override-logo.png', 600, 450),
        ])->assertRedirect();
        Storage::disk('public')->assertExists(LabelPrint::latest('id')->value('logo_path'));
    }
}
