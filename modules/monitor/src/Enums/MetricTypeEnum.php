<?php

namespace Modules\Monitor\Enums;

use App\Traits\EnumDataListTrait;

enum MetricTypeEnum: int
{
    use EnumDataListTrait;

    case login = 1;
    case login_otp = 2;
    case verify_otp = 3;
    case register = 4;
    case payment = 5;
    case search = 6;
    case pdp = 7;

    public static function typesWithOutPayment(): array
    {
        return array_filter(self::values(), static fn($type) => $type !== self::payment->value);
    }

    public function failedStatuses(): array
    {
        return match ($this) {
            self::login_otp => [500, 504],
            self::verify_otp => [500, 504],
            self::register => [500, 504],
            self::payment => [500, 504],
            self::search => [500, 504],
            self::pdp => [500, 504],
        };
    }

    public function validStatuses(): array
    {
        return match ($this) {
            self::login_otp => [200],
            self::verify_otp => [200, 422],
            self::register => [200],
            self::payment => [200],
            self::search => [200],
            self::pdp => [200],
        };
    }
}
