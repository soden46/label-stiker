<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 100mm 100mm; margin: 0; }
        * { box-sizing: border-box; }
        html, body { width: 100mm; margin: 0; padding: 0; color: #050505; font-family: DejaVu Sans, sans-serif; }
        .sticker-page { width: 92mm; padding: 4mm; overflow: hidden; page-break-after: always; }
        .sticker-page.last-page { page-break-after: auto; }
        .sticker-panel { width: 86mm; height: 70mm; padding: 3mm; overflow: hidden; background: #ffc400; }
        table { border-collapse: collapse; table-layout: fixed; }
        .sticker-header { width: 86mm; height: 18mm; }
        .sticker-logo { width: 25%; color: #fff; background: #050505; border: .45mm solid #fff; text-align: center; vertical-align: middle; }
        .sticker-logo strong { display: block; font-size: 15pt; line-height: 1; }
        .sticker-logo small { display: block; margin-top: 1.2mm; padding-top: .6mm; font-size: 3.5pt; border-top: .2mm solid #fff; }
        .sticker-logo.has-image { padding: 1mm; background: #fff; border-color: #fff; }
        .sticker-logo-image { display: block; max-width: 18mm; max-height: 14mm; margin: 0 auto; }
        .sticker-product { width: 75%; padding: 1.5mm 2mm; background: #fff; border-left: 2mm solid #ffc400; text-align: center; vertical-align: middle; overflow: hidden; }
        .sticker-product strong { display: block; max-width: 100%; overflow: hidden; font-size: 13pt; line-height: 1.05; font-weight: bold; white-space: nowrap; }
        .sticker-product strong.is-long { font-size: 10.5pt; }
        .sticker-product small { display: block; margin-top: 1.5mm; font-size: 6pt; white-space: nowrap; }
        .sticker-fields { width: 86mm; }
        .sticker-fields td { text-align: center; overflow: hidden; }
        .sticker-fields-top { margin-top: 2mm; }
        .sticker-fields-top td:nth-child(1) { width: 35%; }
        .sticker-fields-top td:nth-child(2) { width: 38%; }
        .sticker-fields-top td:nth-child(3) { width: 27%; }
        .field-titles td { height: 5mm; padding: 0 .7mm; color: #050505; background: #ffc400; vertical-align: middle; font-size: 5.5pt; font-weight: bold; letter-spacing: .15mm; white-space: nowrap; }
        .field-values td { height: 12mm; padding: 1mm .7mm; background: #fff; border-right: 1.4mm solid #ffc400; vertical-align: middle; font-size: 8pt; line-height: 1.05; font-weight: bold; white-space: nowrap; }
        .field-values td:last-child { border-right: 0; }
        .field-values td.value-po { font-size: 6.7pt; }
        .sticker-fields-bottom { margin-top: 2mm; }
        .sticker-fields-bottom td:nth-child(1) { width: 38%; }
        .sticker-fields-bottom td:nth-child(2) { width: 25%; }
        .sticker-fields-bottom td:nth-child(3) { width: 37%; }
        .sticker-fields-bottom .field-values td { height: 19mm; }
        .sticker-fields-bottom .field-values .sticker-certification { padding: 1mm; color: #050505; background: #ffc400; text-align: left; font-size: 4.5pt; line-height: 1.4; white-space: normal; border: 0; }
        .sticker-barcodes { width: 92mm; height: 12mm; margin-top: 1.7mm; }
        .sticker-barcodes td { width: 50%; height: 12mm; padding: 0 1.5mm; text-align: center; vertical-align: top; }
        .sticker-barcode-canvas { width: 100%; height: 9.2mm; overflow: hidden; }
        .sticker-barcodes small { display: block; margin-top: .4mm; font-size: 4pt; line-height: 1; letter-spacing: .2mm; }
    </style>
</head>
<body>
@foreach($pages as $page)
    @php([$label, $partBarcode, $customerBarcode, $logoDataUri] = [$page['label'], $page['partBarcode'], $page['customerBarcode'], $page['logoDataUri']])
    @php($isPdfLast = $loop->last)
    @include('labels._label')
@endforeach
</body>
</html>
