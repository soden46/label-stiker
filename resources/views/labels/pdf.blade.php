<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: 100mm 100mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100mm;
            margin: 0;
            padding: 0;
            color: #050505;
            font-family: Helvetica, Arial, sans-serif;
        }

        .sticker-page {
            width: 100mm;
            padding: 4mm;
            overflow: hidden;
            page-break-after: always;
        }

        .sticker-page.last-page {
            page-break-after: auto;
        }

        .sticker-panel {
            width: 92mm;
            height: 70mm;
            padding: 3.5mm;
            overflow: hidden;
            background: #ffc400;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .sticker-header {
            width: 85mm;
            height: 16mm;
        }

        .sticker-logo {
            width: 25%;
            color: #fff;
            background: #050505;
            border: .45mm solid #fff;
            text-align: center;
            vertical-align: middle;
        }

        .sticker-logo strong {
            display: block;
            font-size: 14pt;
            line-height: 1;
        }

        .sticker-logo small {
            display: block;
            margin-top: 1.2mm;
            padding-top: .6mm;
            font-size: 3.5pt;
            border-top: .2mm solid #fff;
        }

        .sticker-logo.has-image {
            padding: 1mm;
            background: #fff;
            border-color: #fff;
        }

        .sticker-logo-image {
            display: block;
            max-width: 18mm;
            max-height: 12mm;
            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT NAME + PORT SIZE
        |--------------------------------------------------------------------------
        */

        .sticker-product {
            width: 75%;
            padding: 1.5mm 2mm;
            background: #fff;
            border-left: 4mm solid #ffc400;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }

        .sticker-product strong {
            display: block;
            max-width: 100%;
            overflow: hidden;
            font-size: 18pt;
            line-height: 1.05;
            font-weight: bold;
            white-space: nowrap;
        }

        .sticker-product strong.is-long {
            font-size: 15pt;
        }

        .sticker-product small {
            display: block;
            margin-top: 1mm;
            font-size: 8pt;
            line-height: 1;
            font-weight: normal;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields {
            width: 85mm;
        }

        .sticker-fields td {
            text-align: center;
            overflow: hidden;
        }

        .sticker-fields-top {
            margin-top: 3mm;
        }

        .sticker-fields-top td:nth-child(1) {
            width: 35%;
        }

        .sticker-fields-top td:nth-child(2) {
            width: 38%;
        }

        .sticker-fields-top td:nth-child(3) {
            width: 27%;
        }

        .field-titles td {
            height: 6mm;
            padding: 0 .7mm;
            color: #050505;
            background: #ffc400;
            vertical-align: middle;
            font-size: 7.4pt;
            line-height: 1;
            font-weight: bold;
            letter-spacing: .6mm;
            white-space: nowrap;
        }

        .field-values td {
            height: 12mm;
            padding: 1mm .7mm;
            background: #fff;
            border-right: 1.5mm solid #ffc400;
            vertical-align: middle;
            font-size: 10.5pt;
            line-height: 1.05;
            font-weight: bold;
            white-space: nowrap;
        }

        .field-values td:last-child {
            border-right: 0;
        }

        .field-values td.value-po {
            font-size: 10pt;
        }

        /*
        |--------------------------------------------------------------------------
        | BOTTOM FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields-bottom {
            margin-top: 3mm;
        }

        .sticker-fields-bottom td:nth-child(1) {
            width: 42%;
        }

        .sticker-fields-bottom td:nth-child(2) {
            width: 22%;
        }

        .sticker-fields-bottom td:nth-child(3) {
            width: 36%;
        }

        .sticker-fields-bottom .field-values td {
            height: 13mm;
            font-size: 12pt;
        }

        .sticker-standard {
            padding: 0 0 0 2.5mm !important;
            background: #ffc400 !important;
            color: #050505 !important;
            text-align: left !important;
            vertical-align: bottom !important;
            font-size: 4.3pt !important;
            line-height: 1.25 !important;
            font-weight: bold !important;
            letter-spacing: 0 !important;
            white-space: normal !important;
        }

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        */

        .sticker-barcodes {
            width: 92mm;
            height: 11.5mm;
            margin-top: 1.5mm;
        }

        .sticker-barcodes td {
            width: 50%;
            height: 11.5mm;
            padding: 0 2mm;
            text-align: center;
            vertical-align: top;
        }

        .sticker-barcode-canvas {
            width: 100%;
            height: 11.5mm;
            overflow: hidden;
        }
    </style>
</head>

<body>

@foreach($pages as $page)

    @php([
        $label,
        $partBarcode,
        $customerBarcode,
        $logoDataUri
    ] = [
        $page['label'],
        $page['partBarcode'],
        $page['customerBarcode'],
        $page['logoDataUri']
    ])

    @php($isPdfLast = $loop->last)

    @include('labels._label')

@endforeach

</body>
</html>
