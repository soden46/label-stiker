<div class="sticker-page {{ ($isPdf ?? false) ? 'is-print-layout' : '' }} {{ ($isResult ?? false) ? 'is-result-label' : '' }} {{ ($isPdfFirst ?? false) ? 'first-page' : '' }} {{ ($isPdfLast ?? false) ? 'last-page' : '' }}" style="{{ ($isPdfFirst ?? true) ? '' : 'page-break-before: always;' }}">
    <div class="sticker-body">
        <div class="sticker-panel">
            <table class="sticker-header" role="presentation">
                <colgroup><col style="width:18%"><col style="width:82%"></colgroup>
                <tr>
                    <td class="sticker-logo {{ ($logoDataUri ?? null) ? 'has-image' : '' }}">
                        @if($logoDataUri ?? null)
                            <img class="sticker-logo-image" src="{{ $logoDataUri }}" alt="Logo label">
                        @else
                            <strong>WAF</strong><small>SPARE PARTS</small>
                        @endif
                    </td>
                    <td class="sticker-description">
                        @if($isPdf ?? false)
                            <span class="sticker-description-label">Description.</span>
                            <strong class="{{ mb_strlen($label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '') > 24 ? 'is-long' : '' }}">
                                {{ $label->product_snapshot['description'] ?? $label->product_snapshot['name'] ?? '-' }}
                            </strong>
                        @else
                        <strong class="{{ mb_strlen($label->product_snapshot['name'] ?? '') > 24 ? 'is-long' : '' }}">
                            {{ $label->product_snapshot['name'] ?? '-' }}
                        </strong>
                        <span>{{ $label->product_snapshot['description'] ?? '' }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            @if($isPdf ?? false)
            <table class="sticker-fields sticker-fields-top" role="presentation">
                <colgroup><col style="width:60%"><col style="width:40%"></colgroup>
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
                <colgroup><col style="width:42%"><col style="width:23%"><col style="width:35%"></colgroup>
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
            @else
            <table class="sticker-fields sticker-fields-top" role="presentation">
                <colgroup><col style="width:42%"><col style="width:33%"><col style="width:25%"></colgroup>
                <tr class="field-titles">
                    <td>CUST PART NO.</td>
                    <td>P.O NUMBER.</td>
                    <td>QTY.</td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->customer_part_no }}</td>
                    <td class="value-po">{{ $label->purchase_order_no }}</td>
                    <td>{{ $label->quantity ? number_format($label->quantity, 0, ',', '.').' '.$label->uom : '' }}</td>
                </tr>
            </table>

            <table class="sticker-fields sticker-fields-bottom" role="presentation">
                <colgroup><col style="width:42%"><col style="width:23%"><col style="width:35%"></colgroup>
                <tr class="field-titles">
                    <td>WAF PART NO.</td>
                    <td>CODE.</td>
                    <td class="sticker-standard" rowspan="2">
                        <span>MANUFACTURED TO WAF</span>
                        <span>SPECIFICATIONS , A INDONESIA</span>
                        <span>REGISTERED TRADEMARK</span>
                        <span>ISO 9001 2015 CERTIFIED</span>
                    </td>
                </tr>
                <tr class="field-values">
                    <td>{{ $label->barcode_value }}</td>
                    <td>{{ $label->product_snapshot['supplier_code'] ?? '' }}</td>
                </tr>
            </table>
            @endif
        </div>

        <table class="sticker-barcodes" role="presentation">
            <colgroup><col style="width:50%"><col style="width:50%"></colgroup>
            <tr>
                <td>
                    <div class="sticker-barcode-canvas">{!! $partBarcode !!}</div>
                </td>
                <td>
                    <div class="sticker-barcode-canvas">{!! $catalogBarcode !!}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
