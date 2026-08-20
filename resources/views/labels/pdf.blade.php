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
            font-family: DejaVu Sans, sans-serif;
        }

        .sticker-page {
            width: 92mm;
            padding: 4mm;
            overflow: hidden;
            page-break-after: always;
        }

        .sticker-page.last-page {
            page-break-after: auto;
        }

        .sticker-panel {
            width: 86mm;
            height: 70mm;
            padding: 3mm;
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
            width: 86mm;
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
            border-left: 2mm solid #ffc400;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }

        .sticker-product strong {
            display: block;
            max-width: 100%;
            overflow: hidden;
            font-size: 12pt;
            line-height: 1.05;
            font-weight: bold;
            white-space: nowrap;
        }

        .sticker-product strong.is-long {
            font-size: 10pt;
        }

        /*
         * REVISI:
         * PORT SIZE diperbesar
         */
        .sticker-product small {
            display: block;
            margin-top: 1.5mm;
            font-size: 7.5pt;
            line-height: 1;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields {
            width: 86mm;
        }

        .sticker-fields td {
            text-align: center;
            overflow: hidden;
        }

        .sticker-fields-top {
            margin-top: 2mm;
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
            height: 5mm;
            padding: 0 .7mm;
            color: #050505;
            background: #ffc400;
            vertical-align: middle;
            font-size: 5.5pt;
            font-weight: bold;
            letter-spacing: .15mm;
            white-space: nowrap;
        }

        .field-values td {
            height: 10.5mm;
            padding: 1mm .7mm;
            background: #fff;
            border-right: 1.4mm solid #ffc400;
            vertical-align: middle;
            font-size: 7.4pt;
            line-height: 1.05;
            font-weight: bold;
            white-space: nowrap;
        }

        .field-values td:last-child {
            border-right: 0;
        }

        .field-values td.value-po {
            font-size: 6.7pt;
        }

        /*
        |--------------------------------------------------------------------------
        | BOTTOM FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields-bottom {
            margin-top: 2mm;
        }

        .sticker-fields-bottom td:nth-child(1) {
            width: 28%;
        }

        .sticker-fields-bottom td:nth-child(2) {
            width: 18%;
        }

        .sticker-fields-bottom td:nth-child(3) {
            width: 28%;
        }

        .sticker-fields-bottom td:nth-child(4) {
            width: 26%;
        }

        .sticker-fields-bottom .field-values td {
            height: 10.5mm;
            font-size: 6.2pt;
        }

        /*
        |--------------------------------------------------------------------------
        | ADDRESS
        |--------------------------------------------------------------------------
        */

        .sticker-addresses {
            width: 86mm;
            margin-top: 2mm;
        }

        /*
         * Tinggi sedikit dinaikkan supaya font alamat
         * yang lebih besar tetap muat.
         */
        .sticker-addresses td {
            height: 14.5mm;
            padding: .8mm 1mm;
            background: #fff;
            border-right: 1.4mm solid #ffc400;
            text-align: left;
            vertical-align: top;
            overflow: hidden;
        }

        .sticker-addresses td:last-child {
            border-right: 0;
        }

        /*
         * REVISI:
         * "Alamat Pengirim" dan "Alamat Penerima"
         * diperbesar.
         */
        .sticker-addresses strong {
            display: block;
            margin-bottom: .8mm;
            font-size: 5.5pt;
            line-height: 1.1;
            font-weight: bold;
        }

        /*
         * REVISI:
         * Isi alamat juga diperbesar.
         */
        .sticker-addresses span {
            display: block;
            max-height: 10.2mm;
            overflow: hidden;
            font-size: 5.4pt;
            line-height: 1.15;
        }

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        */

        .sticker-barcodes {
            width: 92mm;
            height: 12mm;
            margin-top: 1.7mm;
        }

        .sticker-barcodes td {
            width: 50%;
            height: 12mm;
            padding: 0 1.5mm;
            text-align: center;
            vertical-align: top;
        }

        .sticker-barcode-canvas {
            width: 100%;
            height: 9.2mm;
            overflow: hidden;
        }

        /*
         * Default tulisan barcode kanan/kiri.
         */
        .sticker-barcodes small {
            display: block;
            margin-top: .4mm;
            font-size: 4pt;
            line-height: 1;
            letter-spacing: .2mm;
        }

        /*
         * REVISI:
         * Part Number di bawah barcode kiri diperbesar.
         */
        .sticker-barcodes td:first-child small {
            margin-top: .5mm;
            font-size: 5.5pt;
            line-height: 1;
            font-weight: bold;
            letter-spacing: .15mm;
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