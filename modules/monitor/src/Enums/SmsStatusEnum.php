<?php

namespace Modules\Monitor\Enums;


use App\Traits\EnumDataListTrait;

enum SmsStatusEnum: string
{
    use EnumDataListTrait;

    case QUEUED = 'queued';
    case SCHEDULED = 'scheduled';
    case SENT = 'sent';
    case FAILED = 'failed';
    case DELIVERED = 'delivered';
    case UNDELIVERED = 'undelivered';
    case CANCELED = 'canceled';
    case BLOCKED = 'blocked';
    case INVALID = 'invalid';
    case AUTH_PROBLEM = 'auth_problem';
}
