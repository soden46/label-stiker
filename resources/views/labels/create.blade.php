@extends('layouts.app')

@section('title', 'Buat Label')
@section('eyebrow', 'CETAK LABEL')
@section('heading', 'Buat label barcode')

@section('content')
<div class="builder-layout" data-label-builder data-products="{{ $products->toJson() }}">
    <form method="POST" action="{{ route('labels.store') }}" enctype="multipart/form-data" class="builder-form">
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
                <button type="button" id="clearProduct" aria-label="Hapus pilihan">x</button>
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
                <div class="field"><label for="deliveryNote">Surat jalan <b>*</b></label><input id="deliveryNote" name="delivery_note_no" value="{{ old('delivery_note_no') }}" placeholder="Contoh: SJ-2026-001" required></div>
                <div class="field"><label for="customerPart">Customer part no. <b>*</b></label><input id="customerPart" name="customer_part_no" value="{{ old('customer_part_no') }}" placeholder="Terisi dari master data" required></div>
                <div class="field"><label for="quantity">Qty <b>*</b></label><input type="number" min="1" id="quantity" name="quantity" value="{{ old('quantity') }}" placeholder="100" required></div>
                <div class="field"><label for="uom">Satuan <b>*</b></label><input id="uom" name="uom" value="{{ old('uom', 'PCS') }}" placeholder="PCS" required></div>
            </div>
            <div class="form-grid delivery-address-grid">
                <div class="field"><label for="senderAddress">Alamat pengirim <b>*</b></label><textarea id="senderAddress" name="sender_address" rows="3" placeholder="Nama perusahaan, alamat, kota" required>{{ old('sender_address') }}</textarea></div>
                <div class="field"><label for="recipientAddress">Alamat penerima <b>*</b></label><textarea id="recipientAddress" name="recipient_address" rows="3" placeholder="Nama penerima, alamat, kota" required>{{ old('recipient_address') }}</textarea></div>
            </div>
            <div class="label-logo-upload"><div><label>Logo khusus label <span>opsional</span></label><p>Kosongkan untuk memakai logo part; jika part tidak punya logo, sistem memakai logo global.</p></div><label class="compact-file-picker"><input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"><span>+ Pilih gambar</span><small>PNG 600x450 px - maks. 1 MB</small></label></div>
        </section>

        <div class="builder-actions">
            <a href="{{ url(auth()->user()->homePath()) }}" class="button button-ghost">Batal</a>
            <button class="button button-primary" id="generateButton" disabled><span>▣</span> Generate label <span>→</span></button>
        </div>
        <p class="secure-note">ⓘ Data label disimpan sebagai riwayat dan dapat dicetak ulang kapan saja.</p>
    </form>

    <aside class="preview-card">
        <div class="preview-heading"><div><p class="eyebrow">LIVE PREVIEW</p><h3>Pratinjau label</h3></div><span>10 x 10 cm</span></div>
        <div class="empty-preview" id="emptyPreview"><div class="barcode-placeholder">|||| ||| || ||||</div><strong>Label menunggu data</strong><p>Pilih part untuk melihat gambaran label.</p></div>
        <div class="preview-content" id="previewContent" hidden>
            <div class="preview-yellow">
                <div class="preview-head"><div class="preview-logo"><strong>WAF</strong><small>SPARE PARTS</small></div><div class="preview-title"><small>Description.</small><strong id="previewDesc">-</strong><b id="previewName">-</b></div></div>
                <div class="preview-cells preview-cells-top"><span><small>Cust No.</small><b id="previewCustomer">-</b></span><span><small>P.O No.</small><b id="previewPo">-</b></span></div>
                <div class="preview-cells preview-cells-bottom"><span><small>WAF No.</small><b id="previewSku">-</b></span><span><small>Qty.</small><b id="previewQty">-</b></span><span class="preview-standard"><b>MANUFACTURED TO WAF<br>SPECIFICATIONS , A INDONESIA<br>REGISTERED TRADEMARK<br>ISO 9001 2015 CERTIFIED</b></span></div>
            </div>
            <div class="fake-barcode"><span>Company.</span><span>Catalog</span><b>||||| || ||| ||||| | |||| || |||</b></div>
        </div>
        <ul class="preview-tips"><li><span>✓</span> Code 128 mudah dibaca scanner kasir</li><li><span>✓</span> Data part dan stok inventory dibekukan saat label dibuat</li><li><span>✓</span> Halaman PDF native 100 x 100 mm</li></ul>
    </aside>
</div>
@endsection
