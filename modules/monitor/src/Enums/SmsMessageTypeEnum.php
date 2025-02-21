<?php

namespace Modules\Monitor\Enums;

use App\Traits\EnumDataListTrait;

enum SmsMessageTypeEnum: string
{
    use EnumDataListTrait;

    case LOGIN_OTP = 'login_otp'; /*** ارسال رمز عبور یکبار مصرف ***/
}
