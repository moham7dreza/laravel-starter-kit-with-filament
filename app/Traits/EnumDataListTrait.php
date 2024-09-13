<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait EnumDataListTrait
{
    public static function list(): array
    {
        return array_map(fn($i) => ['name' => $i->name, 'value' => $i->value], self::cases());
    }

    public static function values(): array
    {
        return array_map(fn($i) => $i->value, self::cases());
    }

    public static function listByName(): array
    {
        return self::totalCases()->pluck('value', 'name')->toArray();
    }

    private static function totalCases(): Collection
    {
        return collect(self::cases());
    }

    public static function listByValue(): array
    {
        return self::totalCases()->pluck('name', 'value')->toArray();
    }

    public static function random()
    {
        return self::totalCases()->random();
    }
}
