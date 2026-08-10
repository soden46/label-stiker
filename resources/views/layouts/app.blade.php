<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $brandName }}</title>
    <link rel="icon" href="{{ $brandFaviconDataUri ?: asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ url(auth()->user()->homePath()) }}">
            <span class="brand-mark {{ $brandLogoDataUri ? 'has-image' : '' }}">@if($brandLogoDataUri)<img src="{{ $brandLogoDataUri }}" alt="{{ $brandName }}">@else{{ strtoupper(mb_substr($brandName, 0, 1)) }}@endif</span>
            <span><strong>{{ $brandName }}</strong><small>Barcode workspace</small></span>
        </a>
        <nav class="nav-list" aria-label="Navigasi utama">
            <p class="nav-label">Workspace</p>
            @if(auth()->user()->canAccess('dashboard.view'))<a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon">DB</span> Dashboard</a>@endif
            @if(auth()->user()->canAccess('inventory.stock_in'))<a href="{{ route('stock.in.create') }}" class="nav-item {{ request()->routeIs('stock.in.*') ? 'active' : '' }}"><span class="nav-icon">IN</span> Stock masuk</a>@endif
            @if(auth()->user()->canAccess('inventory.stock_out'))<a href="{{ route('stock.out.create') }}" class="nav-item {{ request()->routeIs('stock.out.*') ? 'active' : '' }}"><span class="nav-icon">OUT</span> Stock keluar</a>@endif
            @if(auth()->user()->canAccess('labels.create'))<a href="{{ route('labels.create') }}" class="nav-item {{ request()->routeIs('labels.create', 'labels.show') ? 'active' : '' }}"><span class="nav-icon">LB</span> Label barcode</a>@endif
            @if(auth()->user()->canAccess('labels.view'))<a href="{{ route('labels.index') }}" class="nav-item {{ request()->routeIs('labels.index') ? 'active' : '' }}"><span class="nav-icon">PRT</span> Bulk print</a>@endif
            @if(auth()->user()->canAccess('products.view'))<a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="nav-icon">MST</span> Master part</a>@endif
            @if(auth()->user()->canAccess('branding.manage'))<a href="{{ route('settings.edit') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"><span class="nav-icon">SET</span> Pengaturan</a>@endif

            @if(auth()->user()->isSuperAdmin())
                <p class="nav-label nav-label-spaced">Administrasi</p>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="nav-icon">USR</span> Pengguna</a>
                <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}"><span class="nav-icon">RLE</span> Role & akses</a>
            @endif

            @if(auth()->user()->canAccess('purchasing.view') || auth()->user()->canAccess('inventory.view') || auth()->user()->canAccess('manufacturing.view'))
                <p class="nav-label nav-label-spaced">ERP Modules</p>
                @if(auth()->user()->canAccess('purchasing.view'))<span class="nav-item nav-disabled"><span class="nav-icon">PO</span> Purchase order <em>soon</em></span>@endif
                @if(auth()->user()->canAccess('inventory.view'))<span class="nav-item nav-disabled"><span class="nav-icon">STK</span> Stok gudang <em>soon</em></span>@endif
                @if(auth()->user()->canAccess('manufacturing.view'))<span class="nav-item nav-disabled"><span class="nav-icon">MRP</span> Produksi / MRP <em>soon</em></span>@endif
            @endif
        </nav>
        <div class="sidebar-profile">
            <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <a href="{{ route('profile.edit') }}"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->accessRole?->name ?: auth()->user()->role }}</small></a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button title="Keluar">X</button></form>
        </div>
    </aside>
    <main class="main-area">
        <header class="topbar">
            <button class="menu-button" id="menuButton" aria-label="Buka menu">MENU</button>
            <div><p class="eyebrow">@yield('eyebrow', 'CONTROL ROOM')</p><h1>@yield('heading', 'Dashboard')</h1></div>
            @if(auth()->user()->canAccess('labels.create'))<a href="{{ route('labels.create') }}" class="button button-primary topbar-action"><span>+</span> Buat label</a>@endif
        </header>
        <div class="page-content">
            @if(session('success'))<div class="toast-success">OK {{ session('success') }}</div>@endif
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
