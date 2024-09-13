<?php

namespace App\Enums;

use App\Traits\EnumDataListTrait;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusEnum: int implements HasColor, HasLabel, HasIcon
{
    use EnumDataListTrait;

    case inactive = 0;
    case active = 1;

    public static function typesWithValues(): array
    {
        return collect([
            self::inactive->value => 'inactive',
            self::active->value => 'active',
        ])->toArray();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::inactive => __('inactive'),
            self::active => __('active'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::inactive => 'danger',
            self::active => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::inactive => 'heroicon-m-x-circle',
            self::active => 'heroicon-m-check-badge',
        };
    }
}
