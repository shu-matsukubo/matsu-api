<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class DateUtil
{
    private const TZ = 'Asia/Tokyo';

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TZ);
    }

    public static function toDateString(CarbonImmutable $date): string
    {
        return $date->toDateString();
    }

    public static function parseMonth(string $month): CarbonImmutable
    {
        $parsed = CarbonImmutable::createFromFormat('!Y-m', $month, self::TZ);

        return self::validateParsedDate($parsed, $month, 'month', 'Y-m');
    }

    public static function parseDate(string $date, string $field): CarbonImmutable
    {
        $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date, self::TZ);

        return self::validateParsedDate($parsed, $date, $field, 'Y-m-d');
    }

    private static function validateParsedDate(mixed $parsed, string $original, string $field, string $format): CarbonImmutable
    {
        if (! ($parsed instanceof CarbonImmutable) || $parsed->format($format) !== $original) {
            throw ValidationException::withMessages([
                $field => "The $field must be a valid date in $format format.",
            ]);
        }
        return $parsed;
    }

    public static function parseDateValue(mixed $date, string $field): CarbonImmutable
    {
        if ($date instanceof CarbonImmutable) {
            return $date->startOfDay();
        }

        if (is_string($date) || is_numeric($date)) {
            return self::parseDate((string) $date, $field);
        }

        throw ValidationException::withMessages([
            $field => 'The '.$field.' must be a valid date string.',
        ]);
    }

    public static function resolveMonth(?string $month): CarbonImmutable
    {
        return $month
            ? self::parseMonth($month)
            : self::now();
    }

    public static function startOfMonth(CarbonImmutable $date): CarbonImmutable
    {
        return $date->startOfMonth();
    }

    public static function endOfMonth(CarbonImmutable $date): CarbonImmutable
    {
        return $date->endOfMonth();
    }

    public static function max(CarbonImmutable $a, CarbonImmutable $b): CarbonImmutable
    {
        return $a->gte($b) ? $a : $b;
    }

    public static function min(CarbonImmutable $a, CarbonImmutable $b): CarbonImmutable
    {
        return $a->lte($b) ? $a : $b;
    }

    public static function monthDiff(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (($end->year - $start->year) * 12) + ($end->month - $start->month);
    }

    public static function dayOfMonth(CarbonImmutable $date): int
    {
        return $date->day;
    }

    public static function dateInMonth(CarbonImmutable $month, int $day): CarbonImmutable
    {
        return $month->startOfMonth()->setDay(min($day, $month->endOfMonth()->day));
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public static function monthRange(CarbonImmutable $date): array
    {
        return [
            'start' => self::startOfMonth($date),
            'end' => self::endOfMonth($date),
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public static function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        $start = null;
        $end = null;

        if ($startDate) {
            $start = self::parseDate($startDate, 'start_date');
        }

        if ($endDate) {
            $end = self::parseDate($endDate, 'end_date');
        }

        if (! $start && ! $end) {
            $range = self::monthRange(self::now());
        } elseif (! $start) {
            // $end is guaranteed to be non-null here as $start is null and (! $start && ! $end) is false
            $end = self::parseDate((string) $endDate, 'end_date');
            $range = [
                'start' => self::startOfMonth($end),
                'end' => $end,
            ];
        } elseif (! $end) {
            // $start is guaranteed to be non-null here as $end is null and the above conditions are false
            $start = self::parseDate((string) $startDate, 'start_date');
            $range = [
                'start' => $start,
                'end' => self::endOfMonth($start),
            ];
        } else {
            // Both $start and $end are non-null here
            $start = self::parseDate((string) $startDate, 'start_date');
            $end = self::parseDate((string) $endDate, 'end_date');
            $range = [
                'start' => $start,
                'end' => $end,
            ];
        }

        if ($range['start']->gt($range['end'])) {
            throw ValidationException::withMessages([
                'start_date' => 'The start_date must be a date before or equal to end_date.',
            ]);
        }

        return $range;
    }
}
