<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
@page{size:150mm 100mm;margin:5mm 9mm}*{box-sizing:border-box}html,body{margin:0;color:#050505;font-family:Helvetica,Arial,sans-serif}.shipping-card{position:relative;width:132mm;height:90mm;border:.45mm solid #172f3c;border-radius:15mm;overflow:hidden;page-break-inside:avoid}.shipping-card+ .shipping-card{page-break-before:always}.shipping-section{position:absolute;left:14mm;right:12mm;page-break-inside:avoid}.shipping-section.sender{top:6mm}.shipping-section.recipient{top:42mm}.shipping-section h1{margin:0 0 1.5mm;font-size:12pt;line-height:1;letter-spacing:1.4pt}.shipping-section p{margin:0;font-size:8.5pt;font-weight:700;letter-spacing:1.1pt;line-height:1.35;white-space:pre-line}.shipping-section p.company{font-size:9.5pt}
</style></head><body>
@foreach($deliveryOrders as $deliveryOrder)
@php
    $senderLines = array_filter([$company['address'], $company['city_line'], $company['phone'] ? 'TLP. '.$company['phone'] : null]);
    $recipientLines = array_filter([$deliveryOrder->ship_to_project_site, $deliveryOrder->ship_to_address, $deliveryOrder->ship_to_phone ? 'TLP. '.$deliveryOrder->ship_to_phone : null]);
@endphp
<div class="shipping-card"><section class="shipping-section sender"><h1>PENGIRIM</h1><p class="company">{{ $company['name'] }}</p><p>{{ implode("\n", $senderLines) }}</p></section><section class="shipping-section recipient"><h1>PENERIMA</h1><p class="company">{{ $deliveryOrder->ship_to_company }}</p><p>{{ implode("\n", $recipientLines) }}</p></section></div>
@endforeach
</body></html>
