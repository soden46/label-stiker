@extends('layouts.app')

@section('title', 'Buat Label')
@section('eyebrow', 'CETAK LABEL')
@section('heading', 'Buat label barcode')

@section('content')
<div class="builder-layout" data-label-builder data-products="{{ $products->toJson() }}">
    <form method="POST" action="{{ route('labels.store') }}" class="builder-form">
        @csrf
        <div class="builder-intro">
            <span class="live-dot"></span>
            <div><h2>Satu form, langsung jadi.</h2><p>Pilih part dari master data. Detail katalog akan terisi otomatis.</p></div>
        </div>

        @if($errors->any())
            <div class="error-box"><strong>Ada data yang perlu dicek:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="form-step">
            <div class="step-heading"><span>01</span><div><p class="eyebrow">MASTER DATA</p><h3>Pilih part (SKU)</h3></div></div>
            <div class="product-picker">
                <span class="search-icon">⌕</span>
                <input type="search" id="productSearch" autocomplete="off" placeholder="Cari nama part, WAF part no., customer part, atau kode...">
                <button type="button" id="clearProduct" aria-label="Hapus pilihan">×</button>
                <div class="product-results" id="productResults"></div>
            </div>
            <input type="hidden" name="product_id" id="productId" value="{{ old('product_id') }}">
            <div class="selected-product" id="selectedProduct" hidden>
                <div class="part-badge">PT</div>
                <div><small>PART TERPILIH</small><strong id="selectedName"></strong><span id="selectedMeta"></span></div>
                <span class="selected-check">✓</span>
            </div>
        </section>

        <section class="form-step">
            <div class="step-heading"><span>02</span><div><p class="eyebrow">DATA PENGIRIMAN</p><h3>Lengkapi informasi label</h3></div></div>
            <div class="form-grid">
                <div class="field"><label for="purchaseOrder">P.O number <b>*</b></label><input id="purchaseOrder" name="purchase_order_no" value="{{ old('purchase_order_no') }}" placeholder="Contoh: 1011873938" required></div>
                <div class="field"><label for="customerPart">Customer part no. <b>*</b></label><input id="customerPart" name="customer_part_no" value="{{ old('customer_part_no') }}" placeholder="Terisi dari master data" required></div>
                <div class="field"><label for="quantity">Qty <b>*</b></label><input type="number" min="1" id="quantity" name="quantity" value="{{ old('quantity') }}" placeholder="100" required></div>
                <div class="field"><label for="uom">Satuan <b>*</b></label><input id="uom" name="uom" value="{{ old('uom', 'PCS') }}" placeholder="PCS" required></div>
            </div>
        </section>

        <div class="builder-actions">
            <a href="{{ route('dashboard') }}" class="button button-ghost">Batal</a>
            <button class="button button-primary" id="generateButton" disabled><span>▣</span> Generate label <span>→</span></button>
        </div>
        <p class="secure-note">ⓘ Data label disimpan sebagai riwayat dan dapat dicetak ulang kapan saja.</p>
    </form>

    <aside class="preview-card">
        <div class="preview-heading"><div><p class="eyebrow">LIVE PREVIEW</p><h3>Pratinjau label</h3></div><span>10 × 10 cm</span></div>
        <div class="empty-preview" id="emptyPreview"><div class="barcode-placeholder">|||| ||| || ||||</div><strong>Label menunggu data</strong><p>Pilih part untuk melihat gambaran label.</p></div>
        <div class="preview-content" id="previewContent" hidden>
            <div class="preview-yellow">
                <div class="preview-title"><strong id="previewName">—</strong><small id="previewDesc">—</small></div>
                <div class="preview-cells"><span><small>CUST PART.</small><b id="previewCustomer">—</b></span><span><small>P.O NUMBER.</small><b id="previewPo">—</b></span><span><small>QTY.</small><b id="previewQty">—</b></span><span><small>WAF PART NO.</small><b id="previewSku">—</b></span><span><small>CODE.</small><b id="previewCode">—</b></span></div>
            </div>
            <div class="fake-barcode">||||| || ||| ||||| | |||| || |||</div>
        </div>
        <ul class="preview-tips"><li><span>✓</span> Code 128 mudah dibaca scanner kasir</li><li><span>✓</span> Data part dibekukan saat label dibuat</li><li><span>✓</span> Halaman PDF native 100 × 100 mm</li></ul>
    </aside>
</div>
@endsection
