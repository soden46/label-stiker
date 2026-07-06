<?php

namespace App\Http\Controllers;

use App\Models\LabelPrint;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $period = in_array($request->string('period')->toString(), ['month', 'quarter', 'year'])
            ? $request->string('period')->toString()
            : 'month';
        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $quarter = max(1, min(4, (int) $request->integer('quarter', (int) ceil(now()->month / 3))));

        [$start, $end] = $this->range($period, $year, $month, $quarter);
        $base = LabelPrint::query()->whereBetween('created_at', [$start, $end]);

        $metrics = [
            'labels' => (clone $base)->count(),
            'quantity' => (clone $base)->sum('quantity'),
            'products' => (clone $base)->distinct()->count('product_id'),
            'printed' => (clone $base)->whereNotNull('printed_at')->count(),
        ];

        $rows = (clone $base)->get(['created_at', 'quantity']);
        $chart = $this->chart($rows, $period, $start, $end);

        return view('dashboard', [
            'period' => $period,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'rangeLabel' => $this->rangeLabel($period, $start, $end, $quarter),
            'metrics' => $metrics,
            'chart' => $chart,
            'recentPrints' => LabelPrint::with(['product', 'creator'])->latest()->limit(7)->get(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'availableYears' => collect(range(now()->year - 3, now()->year + 1))->reverse(),
        ]);
    }

    private function range(string $period, int $year, int $month, int $quarter): array
    {
        if ($period === 'year') {
            $start = Carbon::create($year, 1, 1)->startOfDay();

            return [$start, $start->copy()->endOfYear()];
        }

        if ($period === 'quarter') {
            $start = Carbon::create($year, (($quarter - 1) * 3) + 1, 1)->startOfDay();

            return [$start, $start->copy()->addMonths(2)->endOfMonth()];
        }

        $start = Carbon::create($year, $month, 1)->startOfDay();

        return [$start, $start->copy()->endOfMonth()];
    }

    private function chart($rows, string $period, Carbon $start, Carbon $end): array
    {
        $points = collect();
        $cursor = $start->copy();
        $format = $period === 'month' ? 'Y-m-d' : 'Y-m';

        while ($cursor <= $end) {
            $key = $cursor->format($format);
            $points->put($key, [
                'label' => $period === 'month' ? $cursor->format('d') : $cursor->translatedFormat('M'),
                'value' => 0,
            ]);
            $period === 'month' ? $cursor->addDay() : $cursor->addMonth();
        }

        foreach ($rows as $row) {
            $key = $row->created_at->format($format);
            if ($points->has($key)) {
                $point = $points->get($key);
                $point['value']++;
                $points->put($key, $point);
            }
        }

        $max = max(1, (int) $points->max('value'));

        return $points->map(fn ($point) => $point + ['height' => max(5, round(($point['value'] / $max) * 100))])->values()->all();
    }

    private function rangeLabel(string $period, Carbon $start, Carbon $end, int $quarter): string
    {
        return match ($period) {
            'year' => 'Tahun '.$start->year,
            'quarter' => 'Quarter '.$quarter.' · '.$start->year,
            default => $start->translatedFormat('F Y'),
        };
    }
}
