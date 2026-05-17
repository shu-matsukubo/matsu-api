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
        return CarbonImmutable::createFromFormat('Y-m', $month, self::TZ);
    }

    public static function parseDate(string $date, string $field): CarbonImmutable
    {
        $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date, self::TZ);

        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw ValidationException::withMessages([
                $field => 'The '.$field.' must be a valid date in Y-m-d format.',
            ]);
        }

        return $parsed;
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

    public static function monthRange(CarbonImmutable $date): array
    {
        return [
            'start' => self::startOfMonth($date),
            'end' => self::endOfMonth($date),
        ];
    }

    public static function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if ($startDate) {
            $start = self::parseDate($startDate, 'start_date');
        }

        if ($endDate) {
            $end = self::parseDate($endDate, 'end_date');
        }

        if (!isset($start) && !isset($end)) {
            $range = self::monthRange(self::now());
        } elseif (!isset($start)) {
            $range = [
                'start' => self::startOfMonth($end),
                'end' => $end,
            ];
        } elseif (!isset($end)) {
            $range = [
                'start' => $start,
                'end' => self::endOfMonth($start),
            ];
        } else {
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
