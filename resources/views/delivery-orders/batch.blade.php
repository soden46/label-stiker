@extends('layouts.app')

@section('title', 'Batch Print DO')
@section('eyebrow', 'PENGIRIMAN')
@section('heading', 'Batch Print DO siap')

@section('content')
<section class="panel batch-ready"><div class="panel-heading"><div><p class="eyebrow">{{ $deliveryOrders->count() }} DELIVERY ORDER</p><h3>Dokumen dibuat dalam urutan pilihan</h3></div></div><div class="stock-form-body"><ol class="batch-order-list">@foreach($deliveryOrders as $deliveryOrder)<li><strong>{{ $deliveryOrder->number }}</strong><span>{{ $deliveryOrder->ship_to_company }}</span></li>@endforeach</ol><p class="batch-explanation">DomPDF menggunakan satu ukuran per file. Flow batch ini mempertahankan urutan yang sama dalam dua PDF: semua DO A4 lalu semua shipping sticker 150 × 100 mm.</p><div class="batch-downloads"><form method="POST" action="{{ route('delivery-orders.batch.pdf') }}">@csrf @foreach($deliveryOrders as $deliveryOrder)<input type="hidden" name="delivery_order_ids[]" value="{{ $deliveryOrder->id }}">@endforeach<button class="button button-primary">▣ Download Batch DO A4</button></form><form method="POST" action="{{ route('delivery-orders.batch.shipping-stickers') }}">@csrf @foreach($deliveryOrders as $deliveryOrder)<input type="hidden" name="delivery_order_ids[]" value="{{ $deliveryOrder->id }}">@endforeach<button class="button button-ghost">▣ Download Batch Shipping Sticker</button></form></div></div></section>
@endsection
