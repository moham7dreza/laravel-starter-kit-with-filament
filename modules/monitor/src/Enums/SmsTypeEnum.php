<?php

namespace Modules\Monitor\Enums;


use App\Traits\EnumDataListTrait;

enum SmsTypeEnum: string
{
    use EnumDataListTrait;

    case SEND = 'send';
    case RECEIVE = 'receive';
}
