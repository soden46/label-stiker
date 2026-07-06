@extends('layouts.app')

@section('title', 'Master Part')
@section('eyebrow', 'KATALOG')
@section('heading', 'Master part')

@section('content')
<div class="catalog-layout">
    <section class="panel catalog-panel">
        <div class="panel-heading"><div><p class="eyebrow">DATABASE PART</p><h3>{{ $products->total() }} part tersimpan</h3></div>
            <form class="table-search"><input type="search" name="search" value="{{ $search }}" placeholder="Cari SKU, nama, customer part..."><button>⌕</button></form>
        </div>
        <div class="table-wrap"><table><thead><tr><th>Part</th><th>Customer part</th><th>Code</th><th>Barcode</th><th>UOM</th><th>Status</th></tr></thead><tbody>
            @foreach($products as $product)<tr><td><strong>{{ $product->name }}</strong><small>{{ $product->sku }} · {{ $product->description }}</small></td><td class="mono">{{ $product->customer_part_no ?: '—' }}</td><td class="mono">{{ $product->supplier_code ?: '—' }}</td><td class="mono">{{ $product->barcode_value }}</td><td>{{ $product->uom }}</td><td><span class="status {{ $product->is_active ? 'done' : 'draft' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td></tr>@endforeach
        </tbody></table></div>
        <div class="pagination">{{ $products->links() }}</div>
    </section>
    <aside class="panel add-product-panel">
        <p class="eyebrow">TAMBAH CEPAT</p><h3>Part baru</h3><p>Masukkan data inti. Nanti bisa dikembangkan menjadi import Excel.</p>
        <form method="POST" action="{{ route('products.store') }}">@csrf
            <label>WAF part no. / SKU *</label><input name="sku" value="{{ old('sku') }}" placeholder="1000-P12-M8" required>
            <label>Nama part *</label><input name="name" value="{{ old('name') }}" placeholder="CON-STRAIGHT" required>
            <label>Deskripsi</label><input name="description" value="{{ old('description') }}" placeholder="SIZE.3/8X1/4NPT">
            <div class="form-grid"><div><label>Customer part</label><input name="customer_part_no" value="{{ old('customer_part_no') }}"></div><div><label>Supplier code</label><input name="supplier_code" value="{{ old('supplier_code') }}"></div></div>
            <div class="form-grid"><div><label>Barcode <span class="optional-label">opsional</span></label><input name="barcode_value" value="{{ old('barcode_value') }}" placeholder="Kosong = gunakan SKU"><small class="input-hint">Isi hanya jika produk sudah punya kode barcode sendiri.</small></div><div><label>UOM *</label><input name="uom" value="{{ old('uom', 'PCS') }}" required></div></div>
            @if($errors->any())<div class="field-error">{{ $errors->first() }}</div>@endif
            <button class="button button-primary button-wide">＋ Simpan part</button>
        </form>
    </aside>
</div>
@endsection
