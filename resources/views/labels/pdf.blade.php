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
            height: 100mm;
            margin: 0;
            padding: 0;
            color: #050505;
            font-family: "Bahnschrift", "Arial Narrow", "DejaVu Sans", Helvetica, Arial, sans-serif;
        }

        .sticker-page {
            width: 100mm;
            padding: 1mm 3mm 0 3mm;
            position: relative;
            overflow: hidden;
            page-break-before: auto;
            page-break-after: auto;
        }

        .sticker-page.first-page {
            page-break-before: auto;
        }

        .sticker-page.last-page {
            page-break-after: auto;
        }

        .sticker-panel {
            width: 89mm;
            height: 65mm;
            padding: 2.5mm;
            overflow: hidden;
            background: #050505;
            border-radius: 5mm 5mm 0 0;
        }

        .sticker-body {
            width: 94mm;
            margin-top: 0;
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
            width: 89mm;
            height: 19mm;
        }

        .sticker-logo {
            color: #fff;
            background: #050505;
            border: .35mm solid #2b5a9e;
            text-align: center;
            vertical-align: middle;
        }

        .sticker-logo strong {
            display: block;
            font-size: 9pt;
            line-height: 1;
            font-weight: 900;
        }

        .sticker-logo small {
            display: block;
            margin-top: .8mm;
            padding-top: .4mm;
            font-size: 3.8pt;
            line-height: 1;
            border-top: .2mm solid #fff;
            font-weight: 700;
        }

        .sticker-logo.has-image {
            padding: .7mm;
            background: #050505;
        }

        .sticker-logo-image {
            display: block;
            max-width: 12mm;
            max-height: 15mm;
            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION
        |--------------------------------------------------------------------------
        */

        .sticker-description {
            padding: 2mm 2mm;
            background: #fff;
            border-left: 3.2mm solid #050505;
            text-align: left;
            vertical-align: top;
            overflow: hidden;
        }

        .sticker-description span,
        .sticker-description strong {
            display: block;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .sticker-description span {
            font-size: 8.5pt;
            line-height: 1;
            font-weight: 900;
        }

        .sticker-description strong {
            margin-top: 1.6mm;
            font-size: 10.5pt;
            line-height: 1;
            font-weight: 900;
        }

        .sticker-description strong.is-long {
            font-size: 8.8pt;
        }

        /*
        |--------------------------------------------------------------------------
        | FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields {
            width: 89mm;
        }

        .sticker-fields td {
            text-align: center;
            overflow: hidden;
        }

        .sticker-fields-top {
            margin-top: 1.8mm;
        }

        .field-titles td {
            height: 4.8mm;
            padding: 0 .7mm;
            color: #fff;
            background: #050505;
            vertical-align: middle;
            font-size: 8pt;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .field-values td {
            height: 15mm;
            padding: 1mm .7mm;
            background: #fff;
            border-right: 2.2mm solid #050505;
            vertical-align: middle;
            font-size: 10.5pt;
            line-height: 1.05;
            font-weight: 900;
            white-space: nowrap;
        }

        .field-values td:last-child {
            border-right: 0;
        }

        .field-values td.value-po {
            font-size: 9.2pt;
        }

        /*
        |--------------------------------------------------------------------------
        | BOTTOM FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields-bottom {
            margin-top: 1.8mm;
        }

        .sticker-fields-bottom .field-values td {
            height: 14mm;
            font-size: 10pt;
        }

        .sticker-standard {
            padding: 0 0 0 2.4mm !important;
            background: #050505 !important;
            color: #fff !important;
            text-align: left !important;
            vertical-align: bottom !important;
            font-size: 5.2pt !important;
            line-height: 1.15 !important;
            font-weight: 900 !important;
            letter-spacing: 0 !important;
            white-space: normal !important;
        }

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        */

        .sticker-barcodes {
            width: 94mm;
            height: 21mm;
            border-left: .25mm solid #8a8a8a;
            border-right: .25mm solid #8a8a8a;
            border-bottom: .25mm solid #8a8a8a;
            background: #fff;
        }

        .sticker-barcodes td {
            width: 50%;
            height: 21mm;
            padding: 1.5mm 3.5mm 1.8mm 3.5mm;
            text-align: center;
            vertical-align: top;
        }

        .barcode-title {
            margin-bottom: .8mm;
            font-size: 8pt;
            line-height: 1;
            font-weight: 900;
            text-align: left;
        }

        .barcode-title-right {
            text-align: right;
        }

        .sticker-barcode-canvas {
            width: 100%;
            height: 14mm;
            overflow: hidden;
        }

        .sticker-barcode-canvas div {
            margin: 0 auto;
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
    @php($isPdfFirst = $loop->first)

    @include('labels._label')

@endforeach

</body>
</html>
