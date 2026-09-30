<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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

    public function pdfDataUri(string $value, float $widthMm = 42, float $heightMm = 9.3): string
    {
        $widthPx = $widthMm * 3.7795275591;
        $heightPx = $heightMm * 3.7795275591;

        return $this->dataUri($value, $widthPx, $heightPx);
    }

    public function qrDataUri(string $value, float $sizeMm = 15): string
    {
        $sizePx = (int) round($sizeMm * 3.7795275591);
        $qrCode = QrCode::create($value)
            ->setSize($sizePx)
            ->setMargin(0)
            ->setBackgroundColor(new Color(255, 196, 0));

        return (new PngWriter)->write($qrCode)->getDataUri();
    }
}
