@extends('layouts.app')

@section('title', 'Preview Label')
@section('eyebrow', 'LABEL SIAP')
@section('heading', 'Pratinjau sebelum cetak')

@section('content')
<div class="label-result-layout">
    <section class="result-stage">
        <div class="ruler ruler-top"><span>10 × 10 cm</span></div>
        @include('labels._label')
    </section>
    <aside class="result-sidebar">
        <span class="success-orb">✓</span>
        <p class="eyebrow">GENERATE BERHASIL</p>
        <h2>Label siap meluncur.</h2>
        <p>Periksa sekali lagi datanya. PDF memakai halaman tetap 100 × 100 mm dan barcode Code 128.</p>
        <div class="printer-profile"><strong>Zebra GC420t</strong><span>203 dpi · Thermal monochrome · 100 × 100 mm</span></div>
        <dl>
            <div><dt>Nomor PO</dt><dd>{{ $label->purchase_order_no }}</dd></div>
            <div><dt>Surat jalan</dt><dd>{{ $label->delivery_note_no ?: '-' }}</dd></div>
            <div><dt>Part</dt><dd>{{ $label->product_snapshot['sku'] }}</dd></div>
            <div><dt>Stock inventory</dt><dd>{{ rtrim(rtrim(number_format((float) $label->inventory_stock, 4, ',', '.'), '0'), ',') }} {{ $label->uom }}</dd></div>
            <div><dt>Dibuat</dt><dd>{{ $label->created_at->translatedFormat('d M Y, H:i') }}</dd></div>
            <div><dt>Status</dt><dd><span class="status {{ $label->printed_at ? 'done' : 'draft' }}">{{ $label->printed_at ? 'Sudah dicetak' : 'Siap cetak' }}</span></dd></div>
        </dl>
        <dl>
            <div><dt>Pengirim</dt><dd>{{ $label->sender_address ?: '-' }}</dd></div>
            <div><dt>Penerima</dt><dd>{{ $label->recipient_address ?: '-' }}</dd></div>
        </dl>
        <a href="{{ route('labels.pdf', $label) }}" class="button button-primary button-wide">▣ Unduh & cetak PDF</a>
        <a href="{{ route('labels.index') }}" class="button button-ghost button-wide">▦ Pilih bulk print</a>
        <a href="{{ route('labels.create') }}" class="button button-ghost button-wide">＋ Buat label lain</a>
        <p class="print-hint">Driver Zebra: pilih kertas <strong>100 × 100 mm</strong>, skala <strong>Actual size / 100%</strong>, margin none, media <strong>Gap/Web</strong>, dan nonaktifkan “Fit to page”.</p>
    </aside>
</div>
@endsection
