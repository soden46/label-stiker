<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Labelin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">L</span>
                <span><strong>labelin</strong><small>Barcode workspace</small></span>
            </a>

            <nav class="nav-list" aria-label="Navigasi utama">
                <p class="nav-label">Workspace</p>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">⌂</span> Dashboard
                </a>
                <a href="{{ route('labels.create') }}" class="nav-item {{ request()->routeIs('labels.create', 'labels.show') ? 'active' : '' }}">
                    <span class="nav-icon">▣</span> Cetak label
                </a>
                <a href="{{ route('labels.index') }}" class="nav-item {{ request()->routeIs('labels.index') ? 'active' : '' }}">
                    <span class="nav-icon">▦</span> Bulk print
                </a>
                <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="nav-icon">◇</span> Master part
                </a>

                <p class="nav-label nav-label-spaced">Segera hadir</p>
                <span class="nav-item nav-disabled"><span class="nav-icon">▤</span> Purchase order <em>soon</em></span>
                <span class="nav-item nav-disabled"><span class="nav-icon">▥</span> Stok gudang <em>soon</em></span>
                <span class="nav-item nav-disabled"><span class="nav-icon">◫</span> Kasir / POS <em>soon</em></span>
            </nav>

            <div class="sidebar-profile">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role }}</small></span>
                <form action="{{ route('logout') }}" method="POST">@csrf<button title="Keluar">↗</button></form>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <button class="menu-button" id="menuButton" aria-label="Buka menu">☰</button>
                <div>
                    <p class="eyebrow">@yield('eyebrow', 'CONTROL ROOM')</p>
                    <h1>@yield('heading', 'Dashboard')</h1>
                </div>
                <a href="{{ route('labels.create') }}" class="button button-primary topbar-action"><span>＋</span> Buat label</a>
            </header>

            <div class="page-content">
                @if(session('success'))
                    <div class="toast-success">✓ {{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
