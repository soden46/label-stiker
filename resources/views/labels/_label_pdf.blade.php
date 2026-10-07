<div class="sticker-page {{ ($isResult ?? false) ? 'is-result-label' : '' }}">
    <div class="sticker-body">
        <div class="sticker-panel">
            <table class="sticker-header" role="presentation">
                <colgroup><col style="width:25.5%"><col style="width:74.5%"></colgroup>
                <tr>
                    <td class="sticker-logo {{ $logoDataUri ? 'has-image' : '' }}">
                        @if($logoDataUri)
                            <img class="sticker-logo-image" src="{{ $logoDataUri }}" alt="Logo label">
                        @else
                            <strong>WAF</strong><small>SPARE PARTS</small>
                        @endif
                    </td>
                    <td class="sticker-description">
                        <span class="sticker-description-label">DESCRIPTION.</span>
                        <strong class="{{ mb_strlen($label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '') > 24 ? 'is-long' : '' }}">
                            {{ $label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '-' }}
                        </strong>
                    </td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-top" role="presentation">
                <colgroup><col style="width:55.5%"><col style="width:44.5%"></colgroup>
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
                <colgroup><col style="width:55.5%"><col style="width:44.5%"></colgroup>
                <tr class="field-titles">
                    <td>WAF NO.</td>
                    <td>QTY.</td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->barcode_value }}</td>
                    <td>{{ $label->quantity ? number_format($label->quantity, 0, ',', '.').' '.$label->uom : '' }}</td>
                </tr>
            </table>

            <table class="sticker-barcodes" role="presentation">
                <colgroup><col style="width:60%"><col style="width:40%"></colgroup>
                <tr>
                    <td>
                        <div class="barcode-standard">
                            <span>MANUFACTURED TO WAF</span>
                            <span>SPECIFICATIONS , A INDONESIA</span>
                            <span>REGISTERED TRADEMARK</span>
                            <span>ISO 9001 2015 CERTIFIED</span>
                        </div>
                    </td>
                    <td>
                        <div class="sticker-barcode-canvas">
                            <img class="pdf-qr" src="{{ $catalogQrDataUri }}" alt="QR code katalog produk">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
