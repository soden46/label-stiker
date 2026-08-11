@extends('layouts.app')

@section('title', 'Bulk Print')
@section('eyebrow', 'PRINT QUEUE')
@section('heading', 'Bulk print label')

@section('content')
<section class="hero-row bulk-hero">
    <div>
        <h2>Cetak banyak, sekali jalan.</h2>
        <p>Pilih label dan tentukan jumlah copy. Setiap copy menjadi satu halaman 100 × 100 mm.</p>
    </div>
    <span class="printer-chip">Zebra GC420t · 203 dpi</span>
    <a href="{{ route('labels.create') }}" class="button button-primary">＋ Buat label baru</a>
</section>

@if($errors->any())
    <div class="error-box bulk-error"><strong>Bulk print belum dapat diproses:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<section class="panel bulk-panel" data-bulk-print>
    <div class="panel-heading bulk-toolbar">
        <div><p class="eyebrow">RIWAYAT LABEL</p><h3>{{ $labels->total() }} label tersedia</h3></div>
        <form method="GET" class="bulk-filters">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari PO, surat jalan, part, atau alamat...">
            <select name="status">
                <option value="">Semua status</option>
                <option value="ready" @selected($status === 'ready')>Siap cetak</option>
                <option value="printed" @selected($status === 'printed')>Pernah dicetak</option>
            </select>
            <button class="button button-ghost">Cari</button>
        </form>
    </div>

    <form method="POST" action="{{ route('labels.bulk-pdf') }}" id="bulkPrintForm">
        @csrf
        <div class="table-wrap">
            <table class="bulk-table">
                <thead>
                    <tr>
                        <th class="check-cell"><input type="checkbox" id="selectAllLabels" aria-label="Pilih semua label di halaman ini"></th>
                        <th>Part</th><th>Nomor PO</th><th>Surat jalan</th><th>Customer part</th><th>Qty produk</th><th>Stock inv.</th><th>Dibuat</th><th>Status</th><th>Copy label</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($labels as $label)
                    <tr>
                        <td class="check-cell"><input class="label-checkbox" type="checkbox" name="label_ids[]" value="{{ $label->id }}" id="label-{{ $label->id }}"></td>
                        <td><label for="label-{{ $label->id }}"><strong>{{ $label->product_snapshot['name'] }}</strong><small>{{ $label->product_snapshot['sku'] }}</small></label></td>
                        <td class="mono">{{ $label->purchase_order_no }}</td>
                        <td class="mono">{{ $label->delivery_note_no ?: '-' }}</td>
                        <td class="mono">{{ $label->customer_part_no }}</td>
                        <td><strong>{{ number_format($label->quantity, 0, ',', '.') }}</strong> {{ $label->uom }}</td>
                        <td><strong>{{ rtrim(rtrim(number_format((float) $label->inventory_stock, 4, ',', '.'), '0'), ',') }}</strong> {{ $label->uom }}</td>
                        <td>{{ $label->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td><span class="status {{ $label->printed_at ? 'done' : 'draft' }}">{{ $label->printed_at ? 'Dicetak' : 'Siap cetak' }}</span></td>
                        <td><div class="copy-control"><button type="button" data-copy-minus>−</button><input class="copy-input" type="number" name="copies[{{ $label->id }}]" value="1" min="1" max="50" disabled><button type="button" data-copy-plus>＋</button></div></td>
                        <td><a class="icon-link" href="{{ route('labels.show', $label) }}" title="Preview">→</a></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty-state">Label tidak ditemukan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="bulk-actionbar">
            <div><strong id="selectedLabelCount">0 label dipilih</strong><small id="selectedPageCount">0 halaman PDF</small></div>
            <span class="bulk-note">Maks. 50 copy/label · 300 halaman/file</span>
            <button class="button button-primary" id="bulkPrintButton" disabled>▦ Generate bulk PDF</button>
        </div>
    </form>
</section>

<div class="pagination bulk-pagination">{{ $labels->links() }}</div>
@endsection
