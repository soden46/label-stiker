<?php

namespace App\Services;

use Picqer\Barcode\Renderers\HtmlRenderer;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

class BarcodeService
{
    public function svg(string $value, float $width = 360, float $height = 72): string
    {
        $barcode = (new TypeCode128)->getBarcode($value);
        $renderer = new SvgRenderer;
        $renderer->setSvgType(SvgRenderer::TYPE_SVG_INLINE);

        return $renderer->render($barcode, $width, $height);
    }

    public function dataUri(string $value, float $width = 360, float $height = 72): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($value, $width, $height));
    }

    public function html(string $value): string
    {
        $barcode = (new TypeCode128)->getBarcode($value);

        return (new HtmlRenderer)->render($barcode, 160, 35);
    }
}
