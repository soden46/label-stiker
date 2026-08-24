@extends('layouts.app')

@section('title', 'Edit Gudang')
@section('eyebrow', 'INVENTORY')
@section('heading', 'Edit gudang')

@section('content')
<div class="edit-product-layout">
    <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="panel edit-product-form">
        @csrf
        @method('PUT')
        <div class="panel-heading">
            <div><p class="eyebrow">DATA GUDANG</p><h3>{{ $warehouse->name }}</h3></div>
            <span class="status {{ $warehouse->is_active ? 'done' : 'draft' }}">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</span>
        </div>

        @if($errors->any())
            <div class="error-box edit-error"><strong>Periksa data berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="edit-form-body">
            <div class="form-grid">
                <div class="field"><label>Kode gudang <b>*</b></label><input name="code" value="{{ old('code', $warehouse->code) }}" required></div>
                <div class="field"><label>Nama gudang <b>*</b></label><input name="name" value="{{ old('name', $warehouse->name) }}" required></div>
            </div>
            <div class="field"><label>Alamat</label><input name="address" value="{{ old('address', $warehouse->address) }}"></div>
            <label class="active-switch"><input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $warehouse->allow_negative_stock))><span></span><div><strong>Izinkan stok minus</strong><small>Matikan jika stok keluar tidak boleh melebihi saldo gudang.</small></div></label>
            <label class="active-switch"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse->is_active))><span></span><div><strong>Gudang aktif</strong><small>Gudang aktif bisa dipilih saat transaksi stok.</small></div></label>
        </div>

        <div class="edit-form-actions">
            <a href="{{ route('warehouses.index') }}" class="button button-ghost">Batal</a>
            <button class="button button-primary">Simpan perubahan</button>
        </div>
    </form>

    <aside class="panel edit-product-note">
        <span>&#9432;</span><h3>Histori stok aman</h3><p>Gudang yang sudah punya transaksi sebaiknya dinonaktifkan, bukan dihapus, supaya riwayat stok tetap bisa dibaca.</p>
        <dl><div><dt>Saldo stok</dt><dd>{{ $warehouse->stockBalances()->count() }}</dd></div><div><dt>Transaksi</dt><dd>{{ $warehouse->stockMovements()->count() }}</dd></div><div><dt>Terakhir diubah</dt><dd>{{ $warehouse->updated_at->diffForHumans() }}</dd></div></dl>
    </aside>
</div>
@endsection
