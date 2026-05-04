<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ExchangeService
{
    /**
     * Get the exact rate from config/env.
     */
    public function getRate(string $from, string $to): float
    {
        $pair = strtolower("{$from}_{$to}");

        // We still use Cache to ensure lightning-fast response times (Requirement 5)
        return Cache::remember("tupay_rate_{$pair}", 60, function () use ($pair) {
            return (float) config("services.tupay.rates.{$pair}");
        });
    }

    /**
     * Perform the conversion
     * Example: 1000 CNY (100000 Fen) to NGN
     * 100000 * 210.50 = 21,050,000 Kobo (210,500 NGN).
     */
    public function convert(int $amount, string $from, string $to): int
    {
        $rate = $this->getRate($from, $to);

        // round() ensures we don't lose precision before casting back to BigInt
        return (int) round($amount * $rate);
    }
}
