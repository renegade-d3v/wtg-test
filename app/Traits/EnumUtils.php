<?php

declare(strict_types=1);

namespace App\Traits;

trait EnumUtils
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function hasValue(string|int $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function hasName(string $name): bool
    {
        return in_array($name, self::names(), true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }

    public static function except(self ...$cases): array
    {
        $excluded = array_map(static fn ($case) => $case->value, $cases);

        return array_filter(self::cases(), static fn ($case) => ! in_array($case->value, $excluded, true));
    }

    public static function only(self ...$cases): array
    {
        $included = array_map(static fn ($case) => $case->value, $cases);

        return array_filter(self::cases(), static fn ($case) => in_array($case->value, $included, true));
    }
}
