<?php

namespace App\Enums;

use App\Traits\EnumDataListTrait;
use Filament\Support\Contracts\HasLabel;

enum LanguageEnum: int implements HasLabel
{
    use EnumDataListTrait;

    case english = 0;
    case farsi = 1;
    case turkish = 2;
    case arabic = 3;
    case france = 4;
    case germany = 5;

    public static function typesWithValues(): array
    {
        return collect([
            self::english->value => __('english'),
            self::farsi->value => __('farsi'),
            self::turkish->value => __('turkish'),
            self::arabic->value => __('arabic'),
            self::france->value => __('france'),
            self::germany->value => __('germany'),
        ])->toArray();
    }

    public static function getDefaultLanguages(): array
    {
        return [
            self::farsi->shortName(),
            self::english->shortName(),
        ];
    }

    public function getLabel(): string
    {
        return $this->shortName();
    }

    public function shortName(): string
    {
        return match ($this) {
            self::english => 'en',
            self::farsi => 'fa',
            self::turkish => 'tr',
            self::arabic => 'ar',
            self::france => 'fr',
            self::germany => 'ge',
            default => ''
        };
    }
}
