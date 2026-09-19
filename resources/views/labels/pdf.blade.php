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
            font-family: Helvetica, Arial, sans-serif;
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
            height: 20mm;
        }

        .sticker-logo {
            width: 18%;
            color: #fff;
            background: #050505;
            border: .35mm solid #2b5a9e;
            text-align: center;
            vertical-align: middle;
        }

        .sticker-logo strong {
            display: block;
            font-size: 7.6pt;
            line-height: 1;
            font-weight: 700;
        }

        .sticker-logo small {
            display: block;
            margin-top: .8mm;
            padding-top: .4mm;
            font-size: 3.4pt;
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
            max-width: 9.5mm;
            max-height: 12mm;
            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION
        |--------------------------------------------------------------------------
        */

        .sticker-description {
            width: 82%;
            padding: 2.1mm 2.8mm;
            background: #fff;
            border-left: 2mm solid #050505;
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
            font-size: 10pt;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 1pt;
        }

        .sticker-description strong {
            margin-top: 1.7mm;
            font-size: 14pt;
            line-height: 1;
            font-weight: 700;
            letter-spacing: .8pt;
        }

        .sticker-description strong.is-long {
            font-size: 12pt;
            letter-spacing: .3pt;
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
            margin-top: 1.3mm;
        }

        .field-titles td {
            height: 4.8mm;
            padding: 0 .7mm;
            color: #fff;
            background: #050505;
            vertical-align: middle;
            font-size: 10.5pt;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 1.3pt;
            white-space: nowrap;
        }

        .field-values td {
            height: 13.5mm;
            padding: 1mm .7mm;
            background: #fff;
            border-right: 2.2mm solid #050505;
            vertical-align: middle;
            font-size: 10.5pt;
            line-height: 1.05;
            font-weight: 700;
            white-space: nowrap;
        }

        .field-values td:last-child {
            border-right: 0;
        }

        .field-values td.value-po {
            font-size: 9.2pt;
        }

        .sticker-fields-top .field-titles td:nth-child(2) {
            letter-spacing: .33pt;
        }

        /*
        |--------------------------------------------------------------------------
        | BOTTOM FIELDS
        |--------------------------------------------------------------------------
        */

        .sticker-fields-bottom {
            margin-top: 1.3mm;
        }

        .sticker-fields-bottom .field-values td {
            height: 12.5mm;
            font-size: 10pt;
        }

        .sticker-fields-bottom .field-titles td:first-child {
            letter-spacing: 1.1pt;
        }

        .sticker-standard {
            padding: 0 0 0 2.4mm !important;
            background: #050505 !important;
            color: #fff !important;
            text-align: left !important;
            vertical-align: bottom !important;
            font-size: 5.8pt !important;
            line-height: 1.08 !important;
            font-weight: 700 !important;
            letter-spacing: .25pt !important;
            white-space: normal !important;
        }

        /*
        |--------------------------------------------------------------------------
        | BARCODE
        |--------------------------------------------------------------------------
        */

        .sticker-barcodes {
            width: 94mm;
            height: 17mm;
            border-left: .25mm solid #8a8a8a;
            border-right: .25mm solid #8a8a8a;
            border-bottom: .25mm solid #8a8a8a;
            background: #fff;
        }

        .sticker-barcodes td {
            width: 50%;
            height: 17mm;
            padding: .8mm 3.5mm 1mm 3.5mm;
            text-align: center;
            vertical-align: top;
        }

        .barcode-title {
            margin-bottom: .5mm;
            font-size: 10.5pt;
            line-height: 1;
            font-weight: 700;
            text-align: left;
            letter-spacing: 1.3pt;
        }

        .barcode-title-right {
            text-align: right;
        }

        .sticker-barcode-canvas {
            width: 100%;
            height: 10.5mm;
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
