@php
    $isIn = $direction === 'in';
    $title = $isIn ? 'Stock Masuk' : 'Stock Keluar';
    $routeName = $isIn ? 'stock.in.store' : 'stock.out.store';
@endphp

@extends('layouts.app')

@section('title', $title)
@section('eyebrow', 'GUDANG')
@section('heading', $title)

@section('content')
<section class="stock-layout">
    <form method="POST" action="{{ route($routeName) }}" class="panel stock-form-panel">
        @csrf
        <div class="panel-heading">
            <div>
                <p class="eyebrow">{{ $isIn ? 'PENERIMAAN' : 'PENGELUARAN' }}</p>
                <h3>{{ $isIn ? 'Catat barang masuk gudang' : 'Catat barang keluar gudang' }}</h3>
            </div>
            <span class="range-pill">{{ $isIn ? 'IN' : 'OUT' }}</span>
        </div>

        @if($errors->any())
            <div class="error-box stock-error"><strong>Ada data yang perlu dicek:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="stock-form-body">
            <div class="form-grid">
                <div class="field">
                    <label for="product_id">Part/SKU <b>*</b></label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Pilih part</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                                {{ $product->sku }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="warehouse_id">Gudang <b>*</b></label>
                    <select id="warehouse_id" name="warehouse_id" required>
                        <option value="">Pilih gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) old('warehouse_id') === $warehouse->id)>
                                {{ $warehouse->code }} - {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($warehouses->isEmpty())
                        <small class="input-hint">
                            Belum ada gudang aktif.
                            @if(auth()->user()->canAccess('inventory.manage'))
                                <a href="{{ route('warehouses.index') }}">Tambah gudang dulu</a>.
                            @else
                                Hubungi admin untuk membuat master gudang.
                            @endif
                        </small>
                    @endif
                </div>
                <div class="field">
                    <label for="quantity">Qty <b>*</b></label>
                    <input id="quantity" type="number" name="quantity" value="{{ old('quantity') }}" min="0.0001" step="0.0001" placeholder="100" required>
                </div>
                <div class="field">
                    <label for="unit_cost">Harga satuan {{ $isIn ? '*' : '' }}</label>
                    <input id="unit_cost" type="number" name="unit_cost" value="{{ old('unit_cost', $isIn ? '' : 0) }}" min="0" step="0.0001" placeholder="0" @required($isIn)>
                </div>
            </div>

            <div class="field stock-notes">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="3" placeholder="{{ $isIn ? 'Contoh: penerimaan supplier / koreksi stok' : 'Contoh: pemakaian produksi / pengiriman gudang' }}">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="edit-form-actions">
            <button class="button button-primary" type="submit" @disabled($warehouses->isEmpty() || $products->isEmpty())>{{ $isIn ? 'Simpan stock masuk' : 'Simpan stock keluar' }}</button>
        </div>
    </form>

    <aside class="panel stock-recent-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">RIWAYAT</p>
                <h3>{{ $isIn ? 'Stock masuk terbaru' : 'Stock keluar terbaru' }}</h3>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Part</th><th>Gudang</th><th>Qty</th><th>Waktu</th></tr></thead>
                <tbody>
                @forelse($recentMovements as $movement)
                    <tr>
                        <td><strong>{{ $movement->product->name }}</strong><small>{{ $movement->product->sku }}</small></td>
                        <td><strong>{{ $movement->warehouse->code }}</strong><small>{{ $movement->warehouse->name }}</small></td>
                        <td><strong>{{ number_format(abs((float) $movement->quantity), 4, ',', '.') }}</strong></td>
                        <td>{{ $movement->occurred_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </aside>
</section>
@endsection
