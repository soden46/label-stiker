<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ $brandName }}</title>
    <link rel="icon" href="{{ $brandFaviconDataUri ?: asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-story">
            <a class="brand brand-light" href="#"><span class="brand-mark {{ $brandLogoDataUri ? 'has-image' : '' }}">@if($brandLogoDataUri)<img src="{{ $brandLogoDataUri }}" alt="{{ $brandName }}">@else{{ strtoupper(mb_substr($brandName, 0, 1)) }}@endif</span><span><strong>{{ $brandName }}</strong><small>Barcode workspace</small></span></a>
            <div>
                <p class="eyebrow">SMALL BUSINESS, SERIOUS SYSTEM</p>
                <h1>Label rapi.<br>Kerja lebih sat-set.</h1>
                <p>Mulai dari cetak barcode hari ini, tumbuh jadi sistem stok dan kasir besok.</p>
            </div>
            <div class="story-stat"><strong>8 × 8</strong><span>cm label presisi<br>siap cetak PDF</span></div>
        </section>
        <section class="login-form-wrap">
            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf
                <p class="eyebrow">WELCOME BACK</p>
                <h2>Masuk ke workspace</h2>
                <p class="muted">Kelola master part, buat label, dan pantau aktivitas.</p>

                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', 'admin@labelin.test') }}" required autofocus>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror

                <label>Password</label>
                <input type="password" name="password" value="password" required>
                <label class="check-row"><input type="checkbox" name="remember"> Ingat saya di perangkat ini</label>
                <button class="button button-primary button-wide">Masuk ke dashboard <span>→</span></button>
                <a class="auth-help-link" href="{{ route('password.request') }}">Lupa password?</a>
                <p class="demo-hint">Demo: admin@labelin.test / password</p>
            </form>
        </section>
    </main>
</body>
</html>
