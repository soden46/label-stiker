@extends('layouts.app')

@section('title', 'Master Gudang')
@section('eyebrow', 'INVENTORY')
@section('heading', 'Master gudang')

@section('content')
@if($errors->any())
    <div class="error-box access-error"><strong>Periksa data berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="catalog-layout">
    <section class="panel catalog-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">DATABASE GUDANG</p><h3>{{ $warehouses->total() }} gudang tersimpan</h3></div>
            <form class="table-search"><input type="search" name="search" value="{{ $search }}" placeholder="Cari kode, nama, alamat..."><button>&#8981;</button></form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Gudang</th><th>Alamat</th><th>Stok minus</th><th>Relasi stok</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td><strong>{{ $warehouse->name }}</strong><small class="mono">{{ $warehouse->code }}</small></td>
                        <td>{{ $warehouse->address ?: '-' }}</td>
                        <td><span class="status {{ $warehouse->allow_negative_stock ? 'draft' : 'done' }}">{{ $warehouse->allow_negative_stock ? 'Diizinkan' : 'Ditahan' }}</span></td>
                        <td><strong>{{ $warehouse->stock_balances_count }}</strong><small>{{ $warehouse->stock_movements_count }} transaksi</small></td>
                        <td><span class="status {{ $warehouse->is_active ? 'done' : 'draft' }}">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="action-button" title="Edit gudang">&#9998;</a>
                                <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Hapus {{ addslashes($warehouse->name) }} dari master gudang?')">@csrf @method('DELETE')<button class="action-button danger" title="Hapus gudang">&times;</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Belum ada gudang. Tambahkan gudang pertama dari form kanan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $warehouses->links() }}</div>
    </section>

    <aside class="panel add-product-panel">
        <p class="eyebrow">TAMBAH CEPAT</p><h3>Gudang baru</h3><p>Gudang aktif akan muncul di form stock masuk dan stock keluar.</p>
        <form method="POST" action="{{ route('warehouses.store') }}">
            @csrf
            <label>Kode gudang *</label><input name="code" value="{{ old('code') }}" placeholder="MAIN" required>
            <label>Nama gudang *</label><input name="name" value="{{ old('name') }}" placeholder="Gudang Utama" required>
            <label>Alamat</label><input name="address" value="{{ old('address') }}" placeholder="Lokasi gudang">
            <label class="active-switch"><input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock'))><span></span><div><strong>Izinkan stok minus</strong><small>Umumnya dimatikan untuk stok fisik.</small></div></label>
            <label class="active-switch"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))><span></span><div><strong>Gudang aktif</strong><small>Gudang aktif bisa dipilih saat transaksi stok.</small></div></label>
            <button class="button button-primary button-wide">Simpan gudang</button>
        </form>
    </aside>
</div>
@endsection
