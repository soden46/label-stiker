<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@font-face {
    font-family: "Anton";
    font-style: normal;
    font-weight: 400;
    src: url("{{ str_replace('\\', '/', public_path('fonts/anton/Anton-Regular.ttf')) }}") format("truetype");
}

@font-face {
    font-family: "Anton";
    font-style: normal;
    font-weight: 700;
    src: url("{{ str_replace('\\', '/', public_path('fonts/anton/Anton-Regular.ttf')) }}") format("truetype");
}

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
    font-family: "Anton", sans-serif;
    font-size: 10pt;
    line-height: 1.1;
}

.sticker-page {
    width: 100mm;
    padding: 0;
}

.sticker-page + .sticker-page {
    page-break-before: always;
}

.sticker-body {
    width: 100mm;
    margin: 0;
    position: relative;
}

.sticker-panel {
    /* DomPDF adds the 5 mm padding outside these dimensions. */
    width: 90mm;
    height: 65mm;
    padding: 5mm;
    background: #d4a000;
    border-radius: 6mm 6mm 0 0;
}

.sticker-header,
.sticker-fields {
    border-collapse: separate;
    border-spacing: 1.5mm 0;
    table-layout: fixed;
}

.sticker-header {
    width: 90mm;
    height: 20mm;
    margin-top: 2mm;
}

.sticker-logo {
    width: 25.5%;
    padding: 0;
    background: #d4a000;
    border: 0;
    text-align: center;
    vertical-align: middle;
}

.sticker-logo-image {
    display: block;
    width: auto;
    height: 20.6mm;
    max-width: 21.5mm;
    max-height: 20.6mm;
    margin: auto;
}

.sticker-logo strong,
.sticker-logo small {
    display: block;
    color: #050505;
}

.sticker-logo strong {
    font-size: 7.6pt;
}

.sticker-logo small {
    font-size: 3.4pt;
}

.sticker-description {
    padding: 3mm 2.5mm;
    background: #fff;
    border: 0;
    text-align: left;
    vertical-align: top;
    /* DomPDF adds the 3 mm top and bottom padding to this height. */
    height: 14mm;
}

.sticker-description-label {
    display: block;
            font-family: Helvetica, Arial, sans-serif;
    font-size: 7.5pt;
    line-height: 1;
    font-weight: 700;
    letter-spacing: 0.9pt;
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
    width: 90mm;
    margin-top: 1.5mm;
}

.sticker-fields td {
    text-align: center;
    overflow: hidden;
    vertical-align: middle;
}

.field-titles td {
    height: 4.8mm;
    padding: 0 1mm 0 0.5mm;
    background: #d4a000;
    color: #050505;
            font-family: Helvetica, Arial, sans-serif;
    font-size: 7.5pt;
    font-weight: 700;
    letter-spacing: 0.5pt;
    text-align: right;
    white-space: nowrap;
}

.field-values td {
    /* Keep the rendered row at 16.5 mm after the 1 mm top/bottom padding. */
    height: 14.5mm;
    padding: 1mm 0.5mm;
    background: #fff;
    border: 0;
    font-size: 13pt;
    line-height: 1.05;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.field-values td.value-po {
    font-size: 13pt;
}

.barcode-standard {
    width: 100%;
    height: 15mm;
    margin: 0;
    color: #050505;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 9pt;
    line-height: 1.1;
    font-weight: 700;
    letter-spacing: -0.1pt;
    text-align: left;
}

.barcode-standard span {
    display: block;
    width: 125%;
    white-space: nowrap;
    transform: scaleX(0.8);
    transform-origin: left top;
}

.sticker-barcodes {
    width: 90mm;
    margin: 5mm 0 0 5mm;
    border-collapse: separate;
    border-spacing: 1.5mm 0;
    table-layout: fixed;
    clear: both;
    page-break-inside: avoid;
}

.sticker-barcodes td {
    height: 18mm;
    padding: 0;
    vertical-align: top;
    text-align: center;
}

.pdf-barcode {
    display: block;
    width: 100%;
    height: 15mm;
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
        $catalogBarcodeDataUri = $page['catalogBarcodeDataUri'];
        $logoDataUri = $page['logoDataUri'];
    @endphp
    @include('labels._label_pdf')
@endforeach
</body>
</html>
