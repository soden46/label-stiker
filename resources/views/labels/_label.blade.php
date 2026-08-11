@php
    $inventoryStock = rtrim(rtrim(number_format((float) $label->inventory_stock, 4, ',', '.'), '0'), ',');
@endphp
<div class="sticker-page {{ ($isPdfLast ?? false) ? 'last-page' : '' }}">
    <div class="sticker-panel">
        <table class="sticker-header" role="presentation">
            <colgroup><col style="width:25%"><col style="width:75%"></colgroup>
            <tr>
                <td class="sticker-logo {{ ($logoDataUri ?? null) ? 'has-image' : '' }}">
                    @if($logoDataUri ?? null)
                        <img class="sticker-logo-image" src="{{ $logoDataUri }}" alt="Logo label">
                    @else
                        <strong>WAF</strong><small>SPARE PARTS</small>
                    @endif
                </td>
                <td class="sticker-product">
                    <strong class="{{ mb_strlen($label->product_snapshot['name']) > 16 ? 'is-long' : '' }}">{{ $label->product_snapshot['name'] }}</strong>
                    <small>{{ $label->product_snapshot['description'] ?: '-' }}</small>
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
            <colgroup><col style="width:28%"><col style="width:18%"><col style="width:28%"><col style="width:26%"></colgroup>
            <tr class="field-titles">
                <td>WAF PART NO.</td>
                <td>CODE.</td>
                <td>SURAT JALAN</td>
                <td>STOCK INV.</td>
            </tr>
            <tr class="field-values">
                <td>{{ $label->product_snapshot['sku'] }}</td>
                <td>{{ $label->product_snapshot['supplier_code'] ?: '-' }}</td>
                <td>{{ $label->delivery_note_no ?: '-' }}</td>
                <td>{{ $inventoryStock }} {{ $label->uom }}</td>
            </tr>
        </table>

        <table class="sticker-addresses" role="presentation">
            <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
            <tr>
                <td><strong>ALAMAT PENGIRIM</strong><span>{{ $label->sender_address ?: '-' }}</span></td>
                <td><strong>ALAMAT PENERIMA</strong><span>{{ $label->recipient_address ?: '-' }}</span></td>
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
