<?php

namespace App\Cms\Views;

use Illuminate\Support\Carbon;

/**
 * Package pricing as shown on cards and detail pages. Amounts are kept as
 * decimal strings end to end; outside its validity dates (Asia/Kolkata) a
 * price falls back to "on request".
 */
class Price
{
    private const SYMBOLS = ['INR' => '₹', 'USD' => '$', 'EUR' => '€'];

    private const UNITS = [
        'per_person' => 'per person',
        'per_couple' => 'per couple',
        'per_group' => 'per group',
        'per_package' => 'per package',
    ];

    public function __construct(private readonly array $price) {}

    public function isOnRequest(?Carbon $today = null): bool
    {
        $mode = $this->price['mode'] ?? 'on_request';
        if ($mode === 'on_request' || ! isset($this->price['amount']) || $this->price['amount'] === '') {
            return true;
        }
        $today ??= Carbon::today();
        $from = $this->price['valid_from'] ?? null;
        $until = $this->price['valid_until'] ?? null;

        $tz = $today->getTimezone(); // validity dates are calendar dates in the site's timezone (Asia/Kolkata)

        return ($from && $today->lt(Carbon::parse($from, $tz)->startOfDay()))
            || ($until && $today->gt(Carbon::parse($until, $tz)->endOfDay()));
    }

    public function isFrom(): bool
    {
        return ($this->price['mode'] ?? '') === 'from';
    }

    public function amount(): string
    {
        return self::format((string) $this->price['amount'], $this->currency());
    }

    public function original(): ?string
    {
        $original = $this->price['original'] ?? null;
        if (! $original || self::compare((string) $original, (string) $this->price['amount']) <= 0) {
            return null;
        }

        return self::format((string) $original, $this->currency());
    }

    public function unit(): string
    {
        return self::UNITS[$this->price['unit'] ?? 'per_person'] ?? 'per person';
    }

    public function note(): ?string
    {
        return filled($this->price['note'] ?? null) ? $this->price['note'] : null;
    }

    public function currency(): string
    {
        return $this->price['currency'] ?? 'INR';
    }

    /** Plain-text label, e.g. "From ₹25,000 per person". */
    public function label(): string
    {
        if ($this->isOnRequest()) {
            return 'Price on request';
        }

        return ($this->isFrom() ? 'From ' : '').$this->amount().' '.$this->unit();
    }

    /** Format a decimal string with Indian digit grouping for INR (₹1,25,000). */
    public static function format(string $amount, string $currency = 'INR'): string
    {
        [$whole, $fraction] = self::split($amount);
        if ($currency === 'INR' && strlen($whole) > 3) {
            $whole = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($whole, 0, -3)).','.substr($whole, -3);
        } else {
            $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);
        }

        return (self::SYMBOLS[$currency] ?? $currency.' ').$whole.($fraction === '00' ? '' : '.'.$fraction);
    }

    /** Compare two non-negative decimal strings without floating point. */
    public static function compare(string $a, string $b): int
    {
        [$aw, $af] = self::split($a);
        [$bw, $bf] = self::split($b);

        return [strlen($aw), $aw, $af] <=> [strlen($bw), $bw, $bf];
    }

    /** @return array{0:string,1:string} whole digits without leading zeros, 2-digit fraction */
    private static function split(string $amount): array
    {
        [$whole, $fraction] = array_pad(explode('.', trim($amount), 2), 2, '');
        $whole = ltrim(preg_replace('/\D/', '', $whole), '0') ?: '0';
        $fraction = substr(preg_replace('/\D/', '', $fraction).'00', 0, 2);

        return [$whole, $fraction];
    }
}
