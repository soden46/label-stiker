@extends('layouts.app')

@section('title', 'Master Part')
@section('eyebrow', 'KATALOG')
@section('heading', 'Master part')

@section('content')
<section class="import-panel">
    <div class="import-copy">
        <span class="import-icon">⇧</span>
        <div><p class="eyebrow">BULK MASTER DATA</p><h3>Import produk dari Excel</h3><p>Upload XLSX, XLS, atau CSV. Header boleh berada di baris mana saja dan akan dideteksi otomatis.</p></div>
    </div>
    <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="import-form">
        @csrf
        <label class="file-picker"><input type="file" name="product_file" accept=".xlsx,.xls,.csv" required><span>⌕ Pilih file Excel</span><small>maks. 10 MB</small></label>
        <button class="button button-primary">⇧ Import sekarang</button>
    </form>
</section>

@if(session('import_result'))
    @php($import = session('import_result'))
    <section class="import-result">
        <div><strong>{{ $import['created'] }}</strong><span>produk baru</span></div>
        <div><strong>{{ $import['updated'] }}</strong><span>diperbarui</span></div>
        <div><strong>{{ $import['skipped'] }}</strong><span>dilewati</span></div>
        @if($import['errors'])
            <details><summary>{{ count($import['errors']) }} catatan import</summary><ul>@foreach($import['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></details>
        @else
            <p>✓ Import selesai tanpa catatan.</p>
        @endif
    </section>
@endif

@error('product_file')<div class="error-box import-error">{{ $message }}</div>@enderror

<div class="catalog-layout">
    <section class="panel catalog-panel">
        <div class="panel-heading"><div><p class="eyebrow">DATABASE PART</p><h3>{{ $products->total() }} part tersimpan</h3></div>
            <form class="table-search"><input type="search" name="search" value="{{ $search }}" placeholder="Cari SKU, nama, customer part..."><button>⌕</button></form>
        </div>
        <div class="table-wrap"><table><thead><tr><th>Part</th><th>Customer part</th><th>Code</th><th>Barcode</th><th>UOM</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
            @foreach($products as $product)<tr><td><strong>{{ $product->name }}</strong><small>{{ $product->sku }} · {{ $product->description }}</small></td><td class="mono">{{ $product->customer_part_no ?: '—' }}</td><td class="mono">{{ $product->supplier_code ?: '—' }}</td><td class="mono">{{ $product->barcode_value }}</td><td>{{ $product->uom }}</td><td><span class="status {{ $product->is_active ? 'done' : 'draft' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="row-actions"><a href="{{ route('products.edit', $product) }}" class="action-button" title="Edit part">✎</a><form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus {{ addslashes($product->name) }} dari master part? Histori label lama tetap disimpan.')">@csrf @method('DELETE')<button class="action-button danger" title="Hapus part">×</button></form></div></td></tr>@endforeach
        </tbody></table></div>
        <div class="pagination">{{ $products->links() }}</div>
    </section>
    <aside class="panel add-product-panel">
        <p class="eyebrow">TAMBAH CEPAT</p><h3>Part baru</h3><p>Masukkan data inti. Nanti bisa dikembangkan menjadi import Excel.</p>
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">@csrf
            <label>WAF part no. / SKU *</label><input name="sku" value="{{ old('sku') }}" placeholder="1000-P12-M8" required>
            <label>Nama part *</label><input name="name" value="{{ old('name') }}" placeholder="CON-STRAIGHT" required>
            <label>Deskripsi</label><input name="description" value="{{ old('description') }}" placeholder="SIZE.3/8X1/4NPT">
            <div class="form-grid"><div><label>Customer part</label><input name="customer_part_no" value="{{ old('customer_part_no') }}"></div><div><label>Supplier code</label><input name="supplier_code" value="{{ old('supplier_code') }}"></div></div>
            <div class="form-grid"><div><label>Barcode <span class="optional-label">opsional</span></label><input name="barcode_value" value="{{ old('barcode_value') }}" placeholder="Kosong = gunakan SKU"><small class="input-hint">Isi hanya jika produk sudah punya kode barcode sendiri.</small></div><div><label>UOM *</label><input name="uom" value="{{ old('uom', 'PCS') }}" required></div></div>
            <label>Logo part <span class="optional-label">opsional</span></label><input class="simple-file-input" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"><small class="input-hint">PNG 600×450 px (4:3), maks. 1 MB.</small>
            @if($errors->any())<div class="field-error">{{ $errors->first() }}</div>@endif
            <button class="button button-primary button-wide">＋ Simpan part</button>
        </form>
    </aside>
</div>
@endsection
