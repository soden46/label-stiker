<?php

namespace Tests\Feature;

use App\Models\LabelPrint;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use App\Services\BarcodeService;
use App\Services\ProductImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductCategoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function catalogPdf(): UploadedFile
    {
        $pdf = Pdf::loadHTML('<h1>Katalog produk</h1>')->output();

        return UploadedFile::fake()->createWithContent('catalog.pdf', $pdf);
    }

    private function product(?ProductCategory $category = null, string $sku = 'CAT-001'): Product
    {
        return Product::create([
            'sku' => $sku, 'name' => 'Part '.$sku, 'barcode_value' => $sku,
            'uom' => 'PCS', 'product_category_id' => $category?->id,
        ]);
    }

    private function createLabel(Product $product): LabelPrint
    {
        $this->post(route('labels.store'), [
            'product_id' => $product->id, 'delivery_note_no' => 'SJ-001',
            'customer_part_no' => 'CUST-001', 'uom' => 'PCS',
            'sender_address' => 'Bekasi', 'recipient_address' => 'Jakarta',
        ])->assertSessionHasNoErrors()->assertRedirect();

        return LabelPrint::latest('id')->firstOrFail();
    }

    public function test_admin_can_manage_pdf_and_link_categories_and_guest_can_open_catalog(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $this->post(route('product-categories.store'), [
            'name' => 'Air Brake', 'catalog_type' => 'pdf', 'catalog_file' => $this->catalogPdf(),
        ])->assertSessionHasNoErrors()->assertRedirect(route('product-categories.index'));
        $category = ProductCategory::firstOrFail();
        Storage::disk('local')->assertExists($category->catalog_path);
        $originalUrl = $category->catalogUrl();
        $this->get(route('product-categories.index'))->assertOk()->assertSee('Air Brake');
        $this->get(route('product-categories.edit', $category))->assertOk()->assertSee('Air Brake');
        // The add form must be blank even when a category exists in the list.
        $this->get(route('product-categories.index'))->assertSee('name="name" value=""', false);

        $this->put(route('product-categories.update', $category), [
            'name' => 'Air Brake Renamed', 'catalog_type' => 'pdf',
        ])->assertSessionHasNoErrors();
        $this->assertSame($originalUrl, $category->fresh()->catalogUrl());
        $this->put(route('product-categories.update', $category), [
            'name' => 'Air Brake Renamed', 'catalog_type' => 'url', 'catalog_url' => 'https://example.com/brake.pdf',
        ])->assertSessionHasNoErrors();
        $this->assertSame('https://example.com/brake.pdf', $category->fresh()->catalogUrl());
        $this->assertNull($category->fresh()->catalog_path);

        $this->delete(route('product-categories.destroy', $category))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
        auth()->logout();
        $response = $this->get($originalUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->get(route('catalogs.products', ['filename' => str_repeat('a', 40).'.pdf']))->assertNotFound();
    }

    public function test_category_rejects_missing_catalog_invalid_files_links_and_duplicate_names(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('product-categories.store'), ['name' => 'Brake', 'catalog_type' => 'pdf'])
            ->assertSessionHasErrors('catalog_file');
        $this->post(route('product-categories.store'), [
            'name' => 'Brake', 'catalog_type' => 'pdf', 'catalog_file' => UploadedFile::fake()->create('fake.pdf', 1, 'text/plain'),
        ])->assertSessionHasErrors('catalog_file');
        $this->post(route('product-categories.store'), [
            'name' => 'Brake', 'catalog_type' => 'url', 'catalog_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('catalog_url');
        ProductCategory::create(['name' => 'Brake', 'catalog_url' => 'https://example.com/catalog']);
        $this->post(route('product-categories.store'), [
            'name' => 'Brake', 'catalog_type' => 'url', 'catalog_url' => 'https://example.com/new',
        ])->assertSessionHasErrors('name');
    }

    public function test_product_category_cannot_be_deleted_while_used_even_by_deleted_parts(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ProductCategory::create(['name' => 'Brake', 'catalog_url' => 'https://example.com/brake']);
        $product = $this->product($category);
        $this->delete(route('product-categories.destroy', $category))->assertSessionHasErrors('category');
        $product->delete();
        $this->delete(route('product-categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
    }

    public function test_category_and_template_routes_respect_existing_master_part_permissions(): void
    {
        $this->get(route('product-categories.index'))->assertRedirect(route('login'));
        $role = Role::create(['name' => 'Reader', 'slug' => 'reader', 'portal' => 'backoffice']);
        $permission = Permission::create(['name' => 'Read parts', 'slug' => 'products.view', 'module' => 'Master Part', 'portal' => 'backoffice']);
        $role->permissions()->attach($permission);
        $this->actingAs(User::factory()->create(['role' => 'operator', 'role_id' => $role->id]));
        $this->get(route('product-categories.index'))->assertOk()->assertDontSee('Simpan kategori');
        $this->post(route('product-categories.store'), [])->assertForbidden();
        $this->get(route('products.import-template'))->assertForbidden();
    }

    public function test_master_part_can_be_assigned_and_reassigned_to_a_valid_category(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ProductCategory::create(['name' => 'Brake', 'catalog_url' => 'https://example.com/brake']);
        $second = ProductCategory::create(['name' => 'Valve', 'catalog_url' => 'https://example.com/valve']);
        $data = ['sku' => 'PART-001', 'name' => 'Valve', 'uom' => 'PCS', 'product_category_id' => $category->id];
        $this->post(route('products.store'), $data)->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $this->assertSame($category->id, $product->product_category_id);
        $this->get(route('products.index'))->assertOk()->assertSee('Brake')->assertSee('Unduh template Excel');
        $this->get(route('products.edit', $product))->assertOk()->assertSee('Kategori produk');
        $this->put(route('products.update', $product), [...$data, 'product_category_id' => $second->id, 'is_active' => true])
            ->assertSessionHasNoErrors();
        $this->assertSame($second->id, $product->fresh()->product_category_id);
        $this->put(route('products.update', $product), [...$data, 'product_category_id' => 999999])
            ->assertSessionHasErrors('product_category_id');
    }

    public function test_downloaded_template_imports_categories_and_preserves_leading_zero_codes(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ProductCategory::create(['name' => 'Air Brake', 'catalog_url' => 'https://example.com/brake']);
        $response = $this->get(route('products.import-template'))->assertOk()
            ->assertDownload('template-import-master-part.xlsx');
        $path = tempnam(sys_get_temp_dir(), 'part-template-');
        try {
            file_put_contents($path, $response->streamedContent());
            $workbook = IOFactory::load($path);
            $sheet = $workbook->getActiveSheet();
            $this->assertSame('KATEGORI PRODUK', $sheet->getCell('H1')->getValue());
            $this->assertSame('@', $sheet->getStyle('A2')->getNumberFormat()->getFormatCode());
            $sheet->setCellValueExplicit('A2', '000123', DataType::TYPE_STRING);
            $sheet->setCellValue('B2', 'Valve');
            $sheet->setCellValueExplicit('D2', '000987', DataType::TYPE_STRING);
            $sheet->setCellValue('H2', 'Air Brake');
            (new Xlsx($workbook))->save($path);
            $result = app(ProductImportService::class)->import($path);
            $this->assertSame(['created' => 1, 'updated' => 0, 'skipped' => 0, 'errors' => []], $result);
            $this->assertDatabaseHas('products', [
                'sku' => '000123', 'customer_part_no' => '000987', 'barcode_value' => '000123',
                'uom' => 'PCS', 'product_category_id' => $category->id,
            ]);
            $workbook->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_import_skips_unknown_category_and_blank_category_preserves_existing_assignment(): void
    {
        $category = ProductCategory::create(['name' => 'Brake', 'catalog_url' => 'https://example.com/brake']);
        $product = $this->product($category, 'OLD-001');
        $path = tempnam(sys_get_temp_dir(), 'part-import-');
        try {
            app(ProductImportService::class)->writeTemplate($path);
            $workbook = IOFactory::load($path);
            $workbook->getActiveSheet()->fromArray([
                ['OLD-001', 'Renamed', null, null, null, null, null, null],
                ['BAD-001', 'Unknown category', null, null, null, null, null, 'Unknown'],
            ], null, 'A2');
            (new Xlsx($workbook))->save($path);
            $result = app(ProductImportService::class)->import($path);
            $this->assertSame(1, $result['updated']);
            $this->assertSame(1, $result['skipped']);
            $this->assertStringContainsString('baris 3', $result['errors'][0]);
            $this->assertSame($category->id, $product->fresh()->product_category_id);
            $this->assertDatabaseMissing('products', ['sku' => 'BAD-001']);
            $workbook->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_label_preview_snapshot_and_single_and_bulk_pdf_use_each_parts_catalog(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $this->post(route('product-categories.store'), [
            'name' => 'Brake', 'catalog_type' => 'pdf', 'catalog_file' => $this->catalogPdf(),
        ])->assertSessionHasNoErrors();
        $category = ProductCategory::firstOrFail();
        $oldUrl = $category->catalogUrl();
        $product = $this->product($category);
        $secondCategory = ProductCategory::create(['name' => 'Valve', 'catalog_url' => 'https://example.com/valve']);
        $second = $this->product($secondCategory, 'CAT-002');
        $this->get(route('labels.create'))->assertOk()->assertViewHas('products', function ($products) use ($oldUrl) {
            return $products[0]['catalog_url'] === $oldUrl && $products[1]['catalog_url'] === 'https://example.com/valve';
        })->assertViewHas('catalogQrs', fn ($qrs) => $qrs->has($oldUrl) && $qrs->has('https://example.com/valve'));
        $firstLabel = $this->createLabel($product);
        $secondLabel = $this->createLabel($second);
        $this->assertSame($oldUrl, $firstLabel->product_snapshot['catalog_url']);

        $this->put(route('product-categories.update', $category), [
            'name' => 'Brake', 'catalog_type' => 'pdf', 'catalog_file' => $this->catalogPdf(),
        ])->assertSessionHasNoErrors();
        $this->assertNotSame($oldUrl, $category->fresh()->catalogUrl());
        $this->get($oldUrl)->assertOk();
        $product->update(['product_category_id' => $secondCategory->id]);
        $this->get(route('labels.show', $firstLabel))->assertOk()
            ->assertViewHas('catalogQrDataUri', app(BarcodeService::class)->qrDataUri($oldUrl));

        $seen = [];
        $this->partialMock(BarcodeService::class, function ($mock) use (&$seen) {
            $mock->shouldReceive('qrDataUri')->andReturnUsing(function ($url) use (&$seen) {
                $seen[] = $url;

                return (new BarcodeService)->qrDataUri($url);
            });
        });
        $response = $this->get(route('labels.pdf', $firstLabel))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->post(route('labels.bulk-pdf'), ['label_ids' => [$firstLabel->id, $secondLabel->id]])
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame([$oldUrl, $oldUrl, 'https://example.com/valve'], $seen);
    }

    public function test_historical_label_without_catalog_snapshot_keeps_its_original_patria_qr(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product();
        $label = $this->createLabel($product);
        $snapshot = $label->product_snapshot;
        unset($snapshot['catalog_url'], $snapshot['category_name']);
        $label->update(['product_snapshot' => $snapshot]);
        $category = ProductCategory::create(['name' => 'New Category', 'catalog_url' => 'https://example.com/new']);
        $product->update(['product_category_id' => $category->id]);
        $this->get(route('labels.show', $label))->assertOk()
            ->assertViewHas('catalogQrDataUri', app(BarcodeService::class)->qrDataUri(route('catalogs.patria')));
    }
}
