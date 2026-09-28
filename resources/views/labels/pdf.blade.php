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
    /* DomPDF resolves this width before the horizontal padding. */
    width: 94mm;
    padding: 2mm 3mm 0 3mm;
}

.sticker-page + .sticker-page {
    page-break-before: always;
}

.sticker-body {
    width: 94mm;
    margin: 0;
    position: relative;
}

.sticker-panel {
    /* 89 + (2 x 2.5) produces the 94 x 72 mm preview panel in DomPDF. */
    width: 89mm;
    height: 67mm;
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
    /* DomPDF adds the 2 mm vertical padding to this content height. */
    height: 14mm;
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

.field-title-spacer {
    padding: 0 !important;
}

.field-values td {
    /* Keep the rendered row at 16.5 mm after the 1 mm top/bottom padding. */
    height: 14.5mm;
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
    height: 14mm;
}

.sticker-standard {
    background: #050505 !important;
    color: #fff !important;
    border-right: 0 !important;
    text-align: left !important;
    vertical-align: top !important;
    padding: 1mm 0 1.2mm 1mm !important;
    font-family: Helvetica, Arial, sans-serif !important;
    font-size: 5.4pt !important;
    line-height: 1.1 !important;
    font-weight: 700 !important;
    letter-spacing: -0.15pt !important;
    white-space: normal !important;
}

.sticker-standard span {
    display: block;
    white-space: nowrap;
}

.sticker-barcodes {
    width: 94mm;
    border-collapse: collapse;
    table-layout: fixed;
    clear: both;
    margin-top: 2mm;
    page-break-inside: avoid;
}

.sticker-barcodes td {
    height: 21mm;
    padding: 0 2mm;
    vertical-align: top;
    text-align: center;
}

.pdf-barcode {
    display: block;
    width: 42mm;
    height: 9.3mm;
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
