@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'RINGKASAN OPERASIONAL')
@section('heading', 'Dashboard label')

@section('content')
<section class="hero-row">
    <div>
        <h2>Halo, {{ explode(' ', auth()->user()->name)[0] }}. Produksi aman? 👋</h2>
        <p>Lihat ritme pembuatan label dan aktivitas terbaru dalam satu tempat.</p>
    </div>
    <form class="period-filter" method="GET">
        <div class="segmented">
            @foreach(['month' => 'Bulanan', 'quarter' => 'Quarter', 'year' => 'Tahunan'] as $value => $label)
                <a href="{{ route('dashboard', ['period' => $value, 'year' => $year, 'month' => $month, 'quarter' => $quarter]) }}" class="{{ $period === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <input type="hidden" name="period" value="{{ $period }}">
        @if($period === 'month')
            <select name="month" onchange="this.form.submit()">
                @foreach(range(1, 12) as $m)<option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(2000, $m)->translatedFormat('F') }}</option>@endforeach
            </select>
        @elseif($period === 'quarter')
            <select name="quarter" onchange="this.form.submit()">
                @foreach(range(1, 4) as $q)<option value="{{ $q }}" @selected($quarter === $q)>Q{{ $q }}</option>@endforeach
            </select>
        @endif
        <select name="year" onchange="this.form.submit()">
            @foreach($availableYears as $y)<option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>@endforeach
        </select>
    </form>
</section>

<section class="metric-grid">
    <article class="metric-card metric-dark">
        <div><span class="metric-icon">▣</span><small>Total label</small></div>
        <strong>{{ number_format($metrics['labels'], 0, ',', '.') }}</strong>
        <p>{{ $rangeLabel }}</p>
    </article>
    <article class="metric-card">
        <div><span class="metric-icon yellow">↗</span><small>Total unit</small></div>
        <strong>{{ number_format($metrics['quantity'], 0, ',', '.') }}</strong>
        <p>Qty dari seluruh label</p>
    </article>
    <article class="metric-card">
        <div><span class="metric-icon green">◇</span><small>Part terpakai</small></div>
        <strong>{{ number_format($metrics['products'], 0, ',', '.') }}</strong>
        <p>dari {{ $activeProducts }} part aktif</p>
    </article>
    <article class="metric-card">
        <div><span class="metric-icon blue">✓</span><small>PDF dicetak</small></div>
        <strong>{{ number_format($metrics['printed'], 0, ',', '.') }}</strong>
        <p>{{ $metrics['labels'] ? round(($metrics['printed'] / $metrics['labels']) * 100) : 0 }}% dari label dibuat</p>
    </article>
</section>

<section class="dashboard-grid">
    <article class="panel chart-panel">
        <div class="panel-heading"><div><p class="eyebrow">VOLUME</p><h3>Aktivitas pembuatan label</h3></div><span class="range-pill">{{ $rangeLabel }}</span></div>
        <div class="chart-wrap">
            <div class="y-axis"><span>tinggi</span><span>sedang</span><span>0</span></div>
            <div class="bar-chart">
                @foreach($chart as $point)
                    <div class="bar-column" title="{{ $point['value'] }} label">
                        <span class="bar-value">{{ $point['value'] ?: '' }}</span>
                        <i style="height: {{ $point['height'] }}%" class="{{ $point['value'] ? '' : 'empty' }}"></i>
                        <small>{{ $point['label'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </article>

    <article class="panel quick-panel">
        <p class="eyebrow">QUICK START</p>
        <h3>Bikin label baru</h3>
        <p>Pilih part, masukkan nomor PO dan jumlah. Sisanya diisi otomatis.</p>
        <div class="mini-label"><span>CODE 128</span><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <a href="{{ route('labels.create') }}" class="button button-primary button-wide">Mulai buat label <span>→</span></a>
    </article>
</section>

<section class="panel recent-panel">
    <div class="panel-heading"><div><p class="eyebrow">RIWAYAT</p><h3>Label terbaru</h3></div><a href="{{ route('labels.create') }}">Buat lagi →</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Part</th><th>Nomor PO</th><th>Customer part</th><th>Qty</th><th>Dibuat</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($recentPrints as $print)
                <tr>
                    <td><strong>{{ $print->product->name }}</strong><small>{{ $print->product->sku }}</small></td>
                    <td class="mono">{{ $print->purchase_order_no }}</td>
                    <td class="mono">{{ $print->customer_part_no }}</td>
                    <td><strong>{{ number_format($print->quantity, 0, ',', '.') }}</strong> {{ $print->uom }}</td>
                    <td>{{ $print->created_at->diffForHumans() }}</td>
                    <td><span class="status {{ $print->printed_at ? 'done' : 'draft' }}">{{ $print->printed_at ? 'Dicetak' : 'Siap cetak' }}</span></td>
                    <td><a class="icon-link" href="{{ route('labels.show', $print) }}">→</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">Belum ada label. Yuk bikin yang pertama.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
