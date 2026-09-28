<div class="sticker-page">
    <div class="sticker-body">
        <div class="sticker-panel">
            <table class="sticker-header" role="presentation">
                <colgroup><col style="width:22%"><col style="width:78%"></colgroup>
                <tr>
                    <td class="sticker-logo {{ $logoDataUri ? 'has-image' : '' }}">
                        @if($logoDataUri)
                            <img class="sticker-logo-image" src="{{ $logoDataUri }}" alt="Logo label">
                        @else
                            <strong>WAF</strong><small>SPARE PARTS</small>
                        @endif
                    </td>
                    <td class="sticker-description">
                        <span class="sticker-description-label">Description.</span>
                        <strong class="{{ mb_strlen($label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '') > 24 ? 'is-long' : '' }}">
                            {{ $label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '-' }}
                        </strong>
                    </td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-top" role="presentation">
                <colgroup><col style="width:58%"><col style="width:42%"></colgroup>
                <tr class="field-titles">
                    <td>CUST NO.</td>
                    <td>P.O NO.</td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->customer_part_no }}</td>
                    <td class="value-po">{{ $label->purchase_order_no }}</td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-bottom" role="presentation">
                <colgroup><col style="width:43%"><col style="width:24%"><col style="width:33%"></colgroup>
                <tr class="field-titles">
                    <td>WAF NO.</td>
                    <td>QTY.</td>
                    <td class="field-title-spacer"></td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->barcode_value }}</td>
                    <td>{{ $label->quantity ? number_format($label->quantity, 0, ',', '.').' '.$label->uom : '' }}</td>
                    <td class="sticker-standard">
                        <span>MANUFACTURED TO WAF</span>
                        <span>SPECIFICATIONS , A INDONESIA</span>
                        <span>REGISTERED TRADEMARK</span>
                        <span>ISO 9001 2015 CERTIFIED</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="sticker-barcodes" role="presentation">
            <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
            <tr>
                <td>
                    <div class="sticker-barcode-canvas">
                        <img class="pdf-barcode" src="{{ $partBarcodeDataUri }}" alt="Part barcode">
                    </div>
                </td>
                <td>
                    <div class="sticker-barcode-canvas">
                        <img class="pdf-barcode" src="{{ $catalogBarcodeDataUri }}" alt="Supplier barcode">
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
