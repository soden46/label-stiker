@extends('layouts.app')

@section('title', $deliveryOrder->exists ? 'Edit Delivery Order' : 'Buat Delivery Order')
@section('eyebrow', 'PENGIRIMAN')
@section('heading', $deliveryOrder->exists ? 'Edit Delivery Order' : 'Buat Delivery Order')

@section('content')
@php
    $existingItems = old('items', $deliveryOrder->exists ? $deliveryOrder->items->map(fn ($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity, 'unit' => $item->unit, 'weight' => $item->weight])->all() : [['product_id' => '', 'quantity' => '', 'unit' => '', 'weight' => '']]);
@endphp
@if($errors->any())<div class="error-box"><strong>DO belum dapat disimpan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $deliveryOrder->exists ? route('delivery-orders.update', $deliveryOrder) : route('delivery-orders.store') }}" class="delivery-order-form" data-delivery-order-form>
    @csrf @if($deliveryOrder->exists) @method('PUT') @endif
    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">DELIVERY INFORMATION</p><h3>{{ $deliveryOrder->exists ? $deliveryOrder->number : 'Nomor dibuat otomatis saat disimpan' }}</h3></div></div><div class="stock-form-body form-grid">
        <div class="field"><label for="deliveryDate">Date <b>*</b></label><input type="date" id="deliveryDate" name="delivery_date" value="{{ old('delivery_date', optional($deliveryOrder->delivery_date)->format('Y-m-d')) }}" required></div>
        <div class="field"><label for="purchaseOrderNo">PO Number</label><input id="purchaseOrderNo" name="purchase_order_no" value="{{ old('purchase_order_no', $deliveryOrder->purchase_order_no) }}"></div>
        <div class="field"><label for="fob">FOB</label><input id="fob" name="fob" value="{{ old('fob', $deliveryOrder->fob) }}"></div>
        <div class="field"><label for="packages">Packages</label><input id="packages" name="packages" value="{{ old('packages', $deliveryOrder->packages) }}"></div>
        <div class="field"><label for="shipVia">Ship Via</label><input id="shipVia" name="ship_via" value="{{ old('ship_via', $deliveryOrder->ship_via) }}"></div>
        <div class="field"><label for="shipment">Shipment</label><input id="shipment" name="shipment" value="{{ old('shipment', $deliveryOrder->shipment) }}"></div>
    </div></section>

    <section class="panel delivery-address-grid"><div class="panel-heading"><div><p class="eyebrow">DELIVERY ADDRESS</p><h3>Tujuan dan penerima</h3></div></div><div class="stock-form-body form-grid">
        <div class="field"><label for="toPartner">To <b>*</b></label><select id="toPartner" name="to_partner_id" required><option value="">Pilih customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-address="{{ $customer->address }}" data-phone="{{ $customer->phone }}" @selected((string) old('to_partner_id', $deliveryOrder->to_partner_id) === (string) $customer->id)>{{ $customer->code }} — {{ $customer->name }}</option>@endforeach</select></div>
        <div class="field"><label for="toProject">Project / Department / Site</label><input id="toProject" name="to_project_site" value="{{ old('to_project_site', $deliveryOrder->to_project_site) }}"></div>
        <div class="field field-span-2"><label for="toAddress">Alamat To <b>*</b></label><textarea id="toAddress" name="to_address" rows="3" required>{{ old('to_address', $deliveryOrder->to_address) }}</textarea></div>
        <div class="field"><label for="shipToPartner">Ship To <b>*</b></label><select id="shipToPartner" name="ship_to_partner_id" required><option value="">Pilih penerima</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-address="{{ $customer->address }}" data-phone="{{ $customer->phone }}" @selected((string) old('ship_to_partner_id', $deliveryOrder->ship_to_partner_id) === (string) $customer->id)>{{ $customer->code }} — {{ $customer->name }}</option>@endforeach</select></div>
        <div class="field"><label for="shipToProject">Project / Department / Site</label><input id="shipToProject" name="ship_to_project_site" value="{{ old('ship_to_project_site', $deliveryOrder->ship_to_project_site) }}"></div>
        <div class="field field-span-2"><label for="shipToAddress">Alamat Ship To <b>*</b></label><textarea id="shipToAddress" name="ship_to_address" rows="3" required>{{ old('ship_to_address', $deliveryOrder->ship_to_address) }}</textarea></div>
        <div class="field"><label for="shipToPhone">Phone</label><input id="shipToPhone" name="ship_to_phone" value="{{ old('ship_to_phone', $deliveryOrder->ship_to_phone) }}"></div>
        <div class="field field-span-2"><label for="description">Description</label><textarea id="description" name="description" rows="3">{{ old('description', $deliveryOrder->description) }}</textarea></div>
    </div></section>

    <section class="panel delivery-address-grid"><div class="panel-heading"><div><p class="eyebrow">ITEMS</p><h3>Produk yang dikirim</h3></div><button class="button button-ghost" type="button" id="addDeliveryOrderItem">＋ Add item</button></div><div class="table-wrap"><table class="bulk-table delivery-items"><thead><tr><th>Product</th><th>WAF Part No</th><th>Cust Parts No</th><th>Catalog</th><th>Qty</th><th>Unit</th><th>Weight</th><th></th></tr></thead><tbody id="deliveryOrderItems"></tbody></table></div></section>
    <div class="bulk-actionbar delivery-form-actions"><a class="button button-ghost" href="{{ $deliveryOrder->exists ? route('delivery-orders.show', $deliveryOrder) : route('delivery-orders.index') }}">Batal</a><button class="button button-primary">Simpan Delivery Order</button></div>
</form>
<template id="deliveryOrderItemTemplate"><tr><td><select data-product name="items[__INDEX__][product_id]" required><option value="">Pilih product</option>@foreach($products as $product)<option value="{{ $product['id'] }}">{{ $product['name'] }} — {{ $product['waf_part_no'] }}</option>@endforeach</select></td><td data-waf>-</td><td data-customer-part>-</td><td data-catalog>-</td><td><input type="number" name="items[__INDEX__][quantity]" min="0.0001" step="0.0001" required></td><td><input data-unit name="items[__INDEX__][unit]" maxlength="20" required></td><td><input type="number" name="items[__INDEX__][weight]" min="0" step="0.0001"></td><td><button class="icon-link" type="button" data-remove-item aria-label="Hapus item">×</button></td></tr></template>
<script type="application/json" id="deliveryOrderProducts">@json($products)</script><script type="application/json" id="deliveryOrderInitialItems">@json($existingItems)</script>
@endsection
