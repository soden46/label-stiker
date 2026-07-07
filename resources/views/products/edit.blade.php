@extends('layouts.app')

@section('title', 'Edit Part')
@section('eyebrow', 'MASTER DATA')
@section('heading', 'Edit part')

@section('content')
<div class="edit-product-layout">
    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="panel edit-product-form">
        @csrf
        @method('PUT')
        <div class="panel-heading">
            <div><p class="eyebrow">DATA PRODUK</p><h3>{{ $product->name }}</h3></div>
            <span class="status {{ $product->is_active ? 'done' : 'draft' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
        </div>

        @if($errors->any())
            <div class="error-box edit-error"><strong>Periksa data berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="edit-form-body">
            <div class="form-grid">
                <div class="field"><label>WAF part no. / SKU <b>*</b></label><input name="sku" value="{{ old('sku', $product->sku) }}" required></div>
                <div class="field"><label>Nama part <b>*</b></label><input name="name" value="{{ old('name', $product->name) }}" required></div>
            </div>
            <div class="field"><label>Deskripsi</label><input name="description" value="{{ old('description', $product->description) }}"></div>
            <div class="form-grid">
                <div class="field"><label>Customer part</label><input name="customer_part_no" value="{{ old('customer_part_no', $product->customer_part_no) }}"></div>
                <div class="field"><label>Supplier code</label><input name="supplier_code" value="{{ old('supplier_code', $product->supplier_code) }}"></div>
            </div>
            <div class="form-grid">
                <div class="field"><label>Barcode <span class="optional-label">opsional</span></label><input name="barcode_value" value="{{ old('barcode_value', $product->barcode_value) }}" placeholder="Kosong = gunakan SKU"></div>
                <div class="field"><label>UOM <b>*</b></label><input name="uom" value="{{ old('uom', $product->uom) }}" required></div>
            </div>
            <div class="field"><label>Logo part <span class="optional-label">opsional</span></label><div class="inline-logo-upload">@if($productLogoDataUri)<img src="{{ $productLogoDataUri }}" alt="Logo part">@else<span>Belum ada logo khusus</span>@endif<input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"></div><small class="input-hint">Disarankan PNG 600×450 px (4:3), maks. 1 MB.</small></div>
            <label class="active-switch"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))><span></span><div><strong>Part aktif</strong><small>Part aktif dapat dipilih saat membuat label baru.</small></div></label>
        </div>

        <div class="edit-form-actions">
            <a href="{{ route('products.index') }}" class="button button-ghost">Batal</a>
            <button class="button button-primary">✓ Simpan perubahan</button>
        </div>
    </form>

    <aside class="panel edit-product-note">
        <span>ⓘ</span><h3>Histori tetap konsisten</h3><p>Perubahan master part hanya berlaku untuk label baru. Label lama memakai snapshot data saat pertama dibuat.</p>
        <dl><div><dt>Label pernah dibuat</dt><dd>{{ $product->labelPrints()->count() }}</dd></div><div><dt>Dibuat</dt><dd>{{ $product->created_at->translatedFormat('d M Y') }}</dd></div><div><dt>Terakhir diubah</dt><dd>{{ $product->updated_at->diffForHumans() }}</dd></div></dl>
    </aside>
</div>
@endsection
