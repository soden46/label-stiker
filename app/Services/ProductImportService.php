<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ProductImportService
{
    private const ALIASES = [
        'sku' => ['SKU', 'WAF PART NO', 'WAF PART NUMBER', 'PART NO', 'PART NUMBER', 'PARTS NUMBER'],
        'name' => ['DESCRIPTION', 'NAME', 'NAMA', 'NAMA PART', 'PRODUCT NAME'],
        'description' => ['REMARKS', 'REMARK', 'DETAIL', 'SPECIFICATION', 'SPECIFICATIONS'],
        'customer_part_no' => ['CUSTOMER PART', 'CUSTOMER PART NO', 'CUSTOMER PART NUMBER', 'CUST PART', 'CUST PART NO'],
        'supplier_code' => ['CODE', 'SUPPLIER CODE', 'KODE SUPPLIER'],
        'barcode_value' => ['BARCODE', 'BARCODE VALUE', 'BARCODE NUMBER', 'KODE BARCODE'],
        'uom' => ['UOM', 'UNIT', 'SATUAN'],
        'category' => ['KATEGORI PRODUK', 'KATEGORI', 'CATEGORY', 'PRODUCT CATEGORY'],
    ];

    public function import(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $spreadsheet = IOFactory::load($path);
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $rows = $sheet->toArray(null, true, true, true);
            [$headerRow, $columns] = $this->findHeader($rows);

            if ($headerRow === null) {
                $result['errors'][] = "Sheet {$sheet->getTitle()}: header produk tidak ditemukan.";

                continue;
            }

            foreach (array_slice($rows, $headerRow, null, true) as $rowNumber => $row) {
                $values = $this->mapRow($row, $columns);

                if ($this->isEmptyRow($values)) {
                    continue;
                }

                if (! $values['name']) {
                    if (! $values['sku'] && ! $values['customer_part_no']) {
                        continue;
                    }

                    $result['skipped']++;

                    continue;
                }

                $skuValue = $this->isPlaceholder($values['sku']) ? null : $values['sku'];
                $sku = $skuValue ?: $values['customer_part_no'];

                if (! $sku) {
                    $result['skipped']++;
                    $result['errors'][] = "Sheet {$sheet->getTitle()} baris {$rowNumber}: SKU dan Customer Part kosong.";

                    continue;
                }

                $barcode = $this->isPlaceholder($values['barcode_value']) ? null : $values['barcode_value'];
                $barcode = $barcode ?: $sku;
                $existing = Product::withTrashed()->where('sku', $sku)->first();
                $barcodeConflict = Product::withTrashed()->where('barcode_value', $barcode)
                    ->when($existing, fn ($query) => $query->whereKeyNot($existing->id))
                    ->exists();

                if ($barcodeConflict) {
                    $result['skipped']++;
                    $result['errors'][] = "Sheet {$sheet->getTitle()} baris {$rowNumber}: barcode {$barcode} sudah dipakai produk lain.";

                    continue;
                }

                try {
                    $categoryData = [];
                    if ($values['category']) {
                        $category = ProductCategory::where('name', $values['category'])->first();
                        if (! $category) {
                            $result['skipped']++;
                            $result['errors'][] = "Sheet {$sheet->getTitle()} baris {$rowNumber}: kategori {$values['category']} belum terdaftar. Buat kategori produk terlebih dahulu.";

                            continue;
                        }
                        $categoryData['product_category_id'] = $category->id;
                    }

                    if ($existing?->trashed()) {
                        $existing->restore();
                    }

                    Product::updateOrCreate(['sku' => $sku], [
                        ...$categoryData,
                        'name' => $values['name'],
                        'description' => $values['description'],
                        'customer_part_no' => $values['customer_part_no'],
                        'supplier_code' => $values['supplier_code'],
                        'barcode_value' => $barcode,
                        'uom' => $values['uom'] ?: 'PCS',
                        'is_active' => true,
                    ]);
                    $result[$existing ? 'updated' : 'created']++;
                } catch (Throwable $exception) {
                    report($exception);
                    $result['skipped']++;
                    $result['errors'][] = "Sheet {$sheet->getTitle()} baris {$rowNumber}: data gagal disimpan.";
                }
            }
        }

        $result['errors'] = array_slice($result['errors'], 0, 10);
        $spreadsheet->disconnectWorksheets();

        return $result;
    }

    public function writeTemplate(string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Part');
        $headers = ['SKU', 'DESCRIPTION', 'REMARKS', 'CUSTOMER PART', 'CODE', 'BARCODE', 'UOM', 'KATEGORI PRODUK'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setARGB('FF111111');
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC400');
        $sheet->getStyle('A1:H1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A2:H1001')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(30);
        foreach (['A' => 24, 'B' => 30, 'C' => 32, 'D' => 24, 'E' => 20, 'F' => 24, 'G' => 12, 'H' => 28, 'J' => 85] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H1001');
        $instructions = [
            'PETUNJUK IMPORT MASTER PART',
            'Isi data mulai baris 2, kolom A sampai H. Jangan ubah header.',
            'SKU dan DESCRIPTION (nama part) wajib diisi.',
            'SKU yang sudah ada akan diperbarui. Simpan kode sebagai teks agar angka nol di depan tetap ada.',
            'BARCODE kosong = SKU. UOM kosong = PCS.',
            'KATEGORI PRODUK: isi nama kategori yang sudah dibuat di menu Kategori Produk.',
            'Kategori kosong mempertahankan kategori part lama; part baru tanpa kategori memakai katalog PATRIA.',
            'Kolom J berisi petunjuk dan daftar kategori, tidak diimport.',
            'KATEGORI YANG TERSEDIA:',
            ...ProductCategory::orderBy('name')->pluck('name')->all(),
        ];
        foreach ($instructions as $index => $instruction) {
            $sheet->setCellValueExplicit('J'.($index + 1), $instruction, DataType::TYPE_STRING);
        }
        $sheet->getStyle('J1')->getFont()->setBold(true);
        $sheet->getStyle('J1:J'.count($instructions))->getAlignment()->setWrapText(true);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd(1, 1)->setPrintArea('A1:H30');
        $sheet->getPageMargins()->setTop(0.3)->setBottom(0.3)->setLeft(0.3)->setRight(0.3);
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    private function findHeader(array $rows): array
    {
        foreach (array_slice($rows, 0, 50, true) as $rowNumber => $row) {
            $columns = [];

            foreach ($row as $column => $value) {
                $header = $this->normalize($value);

                foreach (self::ALIASES as $field => $aliases) {
                    if (! isset($columns[$field]) && in_array($header, $aliases, true)) {
                        $columns[$field] = $column;
                    }
                }
            }

            if (isset($columns['name']) && count($columns) >= 2) {
                return [(int) $rowNumber, $columns];
            }
        }

        return [null, []];
    }

    private function mapRow(array $row, array $columns): array
    {
        $mapped = [];

        foreach (array_keys(self::ALIASES) as $field) {
            $value = isset($columns[$field]) ? ($row[$columns[$field]] ?? null) : null;
            $mapped[$field] = $this->clean($value);
        }

        return $mapped;
    }

    private function normalize(mixed $value): string
    {
        return Str::of((string) $value)
            ->upper()
            ->replace(['.', '_', '-'], ' ')
            ->squish()
            ->toString();
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Str::of((string) $value)->trim()->limit(200, '')->toString() ?: null;
    }

    private function isPlaceholder(?string $value): bool
    {
        return in_array($this->normalize($value), [
            '', 'BARCODE', 'LINK CATALOG PT WAF', 'PARTS NUMBER', 'PART NUMBER',
        ], true);
    }

    private function isEmptyRow(array $values): bool
    {
        return collect($values)->filter(fn ($value) => $value !== null && $value !== '')->isEmpty();
    }
}
