<div class="sticker-page {{ ($isPdfLast ?? false) ? 'last-page' : '' }}">
    <div class="sticker-panel">
        <table class="sticker-header" role="presentation">
            <colgroup><col style="width:25%"><col style="width:75%"></colgroup>
            <tr>
                <td class="sticker-logo"><strong>WAF</strong><small>SPARE PARTS</small></td>
                <td class="sticker-product">
                    <strong class="{{ mb_strlen($label->product_snapshot['name']) > 16 ? 'is-long' : '' }}">{{ $label->product_snapshot['name'] }}</strong>
                    <small>{{ $label->product_snapshot['description'] ?: '—' }}</small>
                </td>
            </tr>
        </table>

        <table class="sticker-fields sticker-fields-top" role="presentation">
            <colgroup><col style="width:35%"><col style="width:38%"><col style="width:27%"></colgroup>
            <tr class="field-titles">
                <td>CUST PART.</td>
                <td>P.O NUMBER.</td>
                <td>QTY.</td>
            </tr>
            <tr class="field-values">
                <td>{{ $label->customer_part_no }}</td>
                <td class="value-po">{{ $label->purchase_order_no }}</td>
                <td>{{ number_format($label->quantity, 0, ',', '.') }} {{ $label->uom }}</td>
            </tr>
        </table>

        <table class="sticker-fields sticker-fields-bottom" role="presentation">
            <colgroup><col style="width:38%"><col style="width:25%"><col style="width:37%"></colgroup>
            <tr class="field-titles">
                <td>WAF PART NO.</td>
                <td>CODE.</td>
                <td>STANDARD</td>
            </tr>
            <tr class="field-values">
                <td>{{ $label->product_snapshot['sku'] }}</td>
                <td>{{ $label->product_snapshot['supplier_code'] ?: '—' }}</td>
                <td class="sticker-certification">MANUFACTURED TO WAF<br>SPECIFICATIONS, A INDONESIA<br>REGISTERED TRADEMARK<br>ISO 9001:2015 CERTIFIED</td>
            </tr>
        </table>
    </div>

    <table class="sticker-barcodes" role="presentation">
        <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
        <tr>
            <td><div class="sticker-barcode-canvas">{!! $customerBarcode !!}</div><small>{{ $label->customer_part_no }}</small></td>
            <td><div class="sticker-barcode-canvas">{!! $partBarcode !!}</div><small>{{ $label->barcode_value }}</small></td>
        </tr>
    </table>
</div>
