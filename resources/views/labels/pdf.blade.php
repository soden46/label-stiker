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
    height: 100mm;
    padding: 0;
    background: #ffc400;
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
    width: 92mm;
    height: 69mm;
    padding: 5mm 4mm;
    background: #ffc400;
    border-radius: 6mm 6mm 0 0;
}

.sticker-header,
.sticker-fields {
    border-collapse: separate;
    border-spacing: 3mm 0;
    table-layout: fixed;
}

.sticker-header {
    width: 96mm;
    height: 20mm;
    margin-top: 2mm;
    border-spacing: 5mm 0;
}

.sticker-logo {
    width: 27.5%;
    padding: 0;
    background: #050505;
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
    font-size: 20pt;
    line-height: 1;
    font-weight: 700;
    letter-spacing: 0.4pt;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.sticker-description strong.is-long {
    font-size: 15pt;
}

.sticker-fields {
    width: 94mm;
    margin-top: 2mm;
}

.sticker-fields td {
    text-align: center;
    overflow: hidden;
    vertical-align: middle;
}

.field-titles td {
    height: 4.8mm;
    padding: 0 1mm 0 0.5mm;
    background: #ffc400;
    color: #050505;
            font-family: Helvetica, Arial, sans-serif;
    font-size: 7.5pt;
    font-weight: 700;
    letter-spacing: 0.5pt;
    text-align: right;
    white-space: nowrap;
}

.field-values td {
    /* DomPDF adds the 1 mm top/bottom padding to this declared height. */
    height: 11.5mm;
    padding: 1mm 0.5mm;
    background: #fff;
    border: 0;
    font-size: 15pt;
    line-height: 1.05;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.field-values td.value-po {
    font-size: 15pt;
}

.sticker-fields-bottom {
    margin-top: 0;
}

.barcode-standard {
    width: 100%;
    height: 15mm;
    margin: 0;
    color: #050505;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.1;
    font-weight: 700;
    letter-spacing: -0.1pt;
    text-align: left;
}

.barcode-standard span {
    display: block;
    width: 125%;
    white-space: nowrap;
    transform: scaleX(1);
    transform-origin: left top;
}

.sticker-barcodes {
    width: 93mm;
    margin: 4mm 0 0;
    position: relative;
    top: 6mm;
    border-collapse: separate;
    border-spacing: 3mm 0;
    table-layout: fixed;
    clear: both;
    page-break-inside: avoid;
}

.sticker-barcodes td {
    height: 24mm;
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

.pdf-qr {
    display: inline-block;
    width: 25mm;
    height: 25mm;
    margin: -3.5mm -3.5mm 0 0;
}

.sticker-barcodes td:last-child {
    text-align: right;
}
</style>
</head>
<body>
@foreach($pages as $index => $page)
    @php
        $label = $page['label'];
        $catalogQrDataUri = $page['catalogQrDataUri'];
        $logoDataUri = $page['logoDataUri'];
    @endphp
    @include('labels._label_pdf')
@endforeach
</body>
</html>
