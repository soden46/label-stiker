@extends('layouts.app')

@section('title', 'Delivery Order')
@section('eyebrow', 'PENGIRIMAN')
@section('heading', 'Delivery Order')

@section('content')
<section class="hero-row bulk-hero">
    <div><h2>Dokumen pengiriman / Delivery Order</h2><p>Pilih beberapa DO untuk menyiapkan PDF A4 dan shipping sticker secara berurutan.</p></div>
    @if(auth()->user()->canAccess('delivery_orders.create'))<a href="{{ route('delivery-orders.create') }}" class="button button-primary">＋ Buat DO</a>@endif
</section>

@if($errors->any())<div class="error-box bulk-error"><strong>Batch belum dapat diproses:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="panel bulk-panel" data-delivery-order-batch>
    <div class="panel-heading bulk-toolbar">
        <div><p class="eyebrow">DAFTAR DELIVERY ORDER</p><h3>{{ $deliveryOrders->total() }} DO tersedia</h3></div>
        <form method="GET" class="bulk-filters"><input type="search" name="search" value="{{ $search }}" placeholder="Cari nomor DO, PO, atau customer..."><button class="button button-ghost">Cari</button></form>
    </div>
    <form method="POST" action="{{ route('delivery-orders.batch') }}" id="deliveryOrderBatchForm">
        @csrf
        <div class="table-wrap"><table class="bulk-table"><thead><tr><th class="check-cell"><input type="checkbox" id="selectAllDeliveryOrders" aria-label="Pilih semua DO"></th><th>Delivery No</th><th>Tanggal</th><th>To</th><th>Ship To</th><th>PO Number</th><th>Total item</th><th></th></tr></thead>
        <tbody>@forelse($deliveryOrders as $deliveryOrder)<tr>
            <td class="check-cell"><input class="delivery-order-checkbox" type="checkbox" name="delivery_order_ids[]" value="{{ $deliveryOrder->id }}" id="delivery-order-{{ $deliveryOrder->id }}"></td>
            <td><label for="delivery-order-{{ $deliveryOrder->id }}"><strong>{{ $deliveryOrder->number }}</strong></label></td>
            <td>{{ $deliveryOrder->delivery_date->translatedFormat('d M Y') }}</td>
            <td>{{ $deliveryOrder->to_company }}</td><td>{{ $deliveryOrder->ship_to_company }}</td>
            <td class="mono">{{ $deliveryOrder->purchase_order_no ?: '-' }}</td><td>{{ $deliveryOrder->items_count }}</td>
            <td><a class="icon-link" href="{{ route('delivery-orders.show', $deliveryOrder) }}" title="Lihat DO">→</a></td>
        </tr>@empty<tr><td colspan="8" class="empty-state">Belum ada Delivery Order.</td></tr>@endforelse</tbody></table></div>
        <div class="bulk-actionbar"><div><strong id="selectedDeliveryOrderCount">0 DO dipilih</strong><small>PDF DO dan sticker akan mengikuti urutan pilihan.</small></div><button class="button button-primary" id="batchDeliveryOrderButton" disabled>▣ Batch Print DO</button></div>
    </form>
</section>
<div class="pagination bulk-pagination">{{ $deliveryOrders->links() }}</div>
@endsection
