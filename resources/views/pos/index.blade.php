<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS · {{ $brandName }}</title>
    <link rel="icon" href="{{ $brandFaviconDataUri ?: asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pos-page">
    <header class="pos-header">
        <a class="brand pos-brand" href="{{ route('pos.index') }}">
            <span class="brand-mark {{ $brandLogoDataUri ? 'has-image' : '' }}">@if($brandLogoDataUri)<img src="{{ $brandLogoDataUri }}" alt="{{ $brandName }}">@else{{ strtoupper(mb_substr($brandName, 0, 1)) }}@endif</span>
            <span><strong>{{ $brandName }} POS</strong><small>Terminal penjualan</small></span>
        </a>
        <div class="pos-header-actions">
            <span class="pos-cashier"><i>{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</i><span><small>Kasir aktif</small><strong>{{ auth()->user()->name }}</strong></span></span>
            <a href="{{ route('profile.edit') }}" class="button button-ghost">Profil</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-ghost">Keluar</button></form>
        </div>
    </header>

    <main class="pos-shell">
        <section class="pos-catalog">
            <div class="pos-title"><div><p class="eyebrow">POINT OF SALE</p><h1>Transaksi baru</h1></div><span>{{ now()->translatedFormat('d M Y · H:i') }}</span></div>
            <label class="pos-search"><span>⌕</span><input type="search" placeholder="Scan barcode atau cari nama/SKU produk..." autofocus><kbd>F2</kbd></label>
            <div class="pos-module-notice"><span>◫</span><div><strong>Terminal POS siap dikembangkan</strong><p>Routing dan item catalog sudah terpisah dari Back Office. Checkout, pembayaran, dan posting stok akan dibangun di atas ledger ERP.</p></div></div>
            <div class="pos-products">
                @forelse($products as $product)
                    <article><span>{{ mb_substr($product->name, 0, 2) }}</span><div><strong>{{ $product->name }}</strong><small>{{ $product->sku }} · {{ $product->uom }}</small><b>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</b></div></article>
                @empty
                    <div class="empty-state">Belum ada produk aktif untuk dijual.</div>
                @endforelse
            </div>
        </section>

        <aside class="pos-cart">
            <div class="pos-cart-title"><div><p class="eyebrow">CURRENT ORDER</p><h2>Keranjang</h2></div><span>0 item</span></div>
            <div class="pos-empty-cart"><span>▣</span><strong>Keranjang masih kosong</strong><p>Scan barcode atau pilih produk untuk memulai transaksi.</p></div>
            <div class="pos-total"><div><span>Subtotal</span><strong>Rp 0</strong></div><div><span>Diskon</span><strong>Rp 0</strong></div><div class="grand"><span>Total</span><strong>Rp 0</strong></div><button disabled class="button button-primary button-wide">Bayar · F8</button></div>
        </aside>
    </main>
</body>
</html>
