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
}
