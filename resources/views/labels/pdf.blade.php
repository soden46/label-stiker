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

html, body {
    width: 100mm;
    height: 100mm;
    margin: 0;
    padding: 0;
    color: #050505;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 9pt;
    line-height: 1.1;
}

.sticker-page {
    width: 100mm;
    min-height: 97mm;
    padding: 3mm 3mm 0 3mm;
}

.sticker-page + .sticker-page {
    page-break-before: always;
}

.sticker-body {
    width: 94mm;
    height: 97mm;
    position: relative;
}

.sticker-panel {
    width: 94mm;
    height: 66mm;
    padding: 2.5mm;
    background: #050505;
    border-radius: 8mm 8mm 0 0;
}

.sticker-header,
.sticker-fields {
    width: 89mm;
    border-collapse: collapse;
    table-layout: fixed;
}

.sticker-header {
    height: 18mm;
}

.sticker-logo {
    width: 22%;
    padding: 0.7mm;
    background: #050505;
    border: 0.35mm solid #050505;
    text-align: center;
    vertical-align: middle;
}

.sticker-logo-image {
    display: block;
    max-width: 15.5mm;
    max-height: 13.5mm;
    margin: auto;
}

.sticker-logo strong,
.sticker-logo small {
    display: block;
    color: #fff;
}

.sticker-logo strong {
    font-size: 7.6pt;
}

.sticker-logo small {
    font-size: 3.4pt;
}

.sticker-description {
    padding: 2mm 2.8mm;
    background: #fff;
    border-left: 2mm solid #050505;
    text-align: left;
    vertical-align: top;
    height: 18mm;
}

.sticker-description-label {
    display: block;
    font-size: 7pt;
    line-height: 1;
    font-weight: 700;
    letter-spacing: 0.8pt;
    margin-bottom: 0.5mm;
}

.sticker-description strong {
    display: block;
    font-size: 13pt;
    line-height: 1;
    font-weight: 700;
    letter-spacing: 0.4pt;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.sticker-description strong.is-long {
    font-size: 10.5pt;
}

.sticker-fields {
    margin-top: 1.3mm;
}

.sticker-fields td {
    text-align: center;
    overflow: hidden;
    vertical-align: middle;
}

.field-titles td {
    height: 4.8mm;
    padding: 0 0.5mm;
    background: #050505;
    color: #fff;
    font-size: 8.2pt;
    font-weight: 700;
    letter-spacing: 0.8pt;
    white-space: nowrap;
}

.field-values td {
    height: 15.5mm;
    padding: 1mm 0.5mm;
    background: #fff;
    border-right: 2.2mm solid #050505;
    font-size: 9.8pt;
    line-height: 1.05;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.field-values td:last-child {
    border-right: 0;
}

.field-values td.value-po {
    font-size: 8.8pt;
}

.sticker-fields-bottom .field-values td {
    height: 16.5mm;
}

.sticker-standard {
    background: #050505 !important;
    color: #fff !important;
    border-right: 0 !important;
    text-align: left !important;
    vertical-align: top !important;
    padding: 1mm 1.5mm !important;
    font-size: 4.2pt !important;
    line-height: 1.15 !important;
    font-weight: 400 !important;
    letter-spacing: 0.3pt !important;
    white-space: normal !important;
}

.sticker-standard span {
    display: block;
}

.sticker-barcodes {
    width: 89mm;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 1mm;
}

.sticker-barcodes td {
    width: 44.5mm;
    height: 18mm;
    padding: 0 1mm;
    vertical-align: middle;
    text-align: center;
}

.pdf-barcode {
    display: block;
    width: 44mm;
    height: 13mm;
    margin: 0 auto;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}
</style>
</head>
<body>
@foreach($pages as $index => $page)
    @php
        $label = $page['label'];
        $partBarcodeDataUri = $page['partBarcodeDataUri'];
        $catalogBarcodeDataUri = $page['catalogBarcodeDataUri'];
        $logoDataUri = $page['logoDataUri'];
    @endphp
    @include('labels._label_pdf')
@endforeach
</body>
</html>