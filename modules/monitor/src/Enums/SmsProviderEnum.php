<?php

namespace Modules\Monitor\Enums;


use App\Traits\EnumDataListTrait;

enum SmsProviderEnum: string
{
    use EnumDataListTrait;

    case DEBUG = 'debug';
    case KAVENEGAR = 'kavenegar';
    case MEDIANA = 'mediana';
}
