<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberSequenceService
{
    public function next(string $key, string $prefix, ?string $period = null, int $padding = 5, string $separator = '-'): string
    {
        $period ??= now()->format('Ym');

        return DB::transaction(function () use ($key, $prefix, $period, $padding, $separator) {
            $sequence = NumberSequence::firstOrCreate(
                ['key' => $key, 'period' => $period],
                ['prefix' => $prefix, 'next_number' => 1, 'padding' => $padding],
            );
            $sequence = NumberSequence::whereKey($sequence->id)->lockForUpdate()->firstOrFail();
            $number = $sequence->next_number;
            $sequence->update(['next_number' => $number + 1, 'prefix' => $prefix, 'padding' => $padding]);

            return $prefix.$period.$separator.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
        }, 3);
    }
}
