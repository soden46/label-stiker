<div class="sticker-page {{ ($isPdfFirst ?? false) ? 'first-page' : '' }} {{ ($isPdfLast ?? false) ? 'last-page' : '' }}" style="{{ ($isPdfFirst ?? true) ? '' : 'page-break-before: always;' }}">
    <div class="sticker-body">
        <div class="sticker-panel">
            <table class="sticker-header" role="presentation">
                <colgroup><col style="width:22%"><col style="width:78%"></colgroup>
                <tr>
                    <td class="sticker-logo {{ ($logoDataUri ?? null) ? 'has-image' : '' }}">
                        @if($logoDataUri ?? null)
                            <img class="sticker-logo-image" src="{{ $logoDataUri }}" alt="Logo label">
                        @else
                            <strong>WAF</strong><small>SPARE PARTS</small>
                        @endif
                    </td>
                    <td class="sticker-description">
                        <span>Description.</span>
                        <strong class="{{ mb_strlen($label->product_snapshot['description'] ?: ($label->product_snapshot['name'] ?? '')) > 34 ? 'is-long' : '' }}">
                            {{ $label->product_snapshot['description'] ?: ($label->product_snapshot['name'] ?? '-') }}
                        </strong>
                    </td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-top" role="presentation">
                <colgroup><col style="width:58%"><col style="width:42%"></colgroup>
                <tr class="field-titles">
                    <td>Cust No.</td>
                    <td>P.O No.</td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->customer_part_no }}</td>
                    <td class="value-po">{{ $label->purchase_order_no }}</td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-bottom" role="presentation">
                <colgroup><col style="width:45%"><col style="width:25%"><col style="width:30%"></colgroup>
                <tr class="field-titles">
                    <td>WAF No.</td>
                    <td>Qty.</td>
                    <td class="sticker-standard" rowspan="2">
                        MANUFACTURED TO WAF<br>
                        SPECIFICATIONS , A INDONESIA<br>
                        REGISTERED TRADEMARK<br>
                        ISO 9001 2015 CERTIFIED
                    </td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->product_snapshot['sku'] }}</td>
                    <td>{{ number_format($label->quantity, 0, ',', '.') }} {{ $label->uom }}</td>
                </tr>
            </table>
        </div>

        <table class="sticker-barcodes" role="presentation">
            <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
            <tr>
                <td>
                    <div class="barcode-title">Company.</div>
                    <div class="sticker-barcode-canvas">{!! $customerBarcode !!}</div>
                </td>
                <td>
                    <div class="barcode-title barcode-title-right">Catalog</div>
                    <div class="sticker-barcode-canvas">{!! $partBarcode !!}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
