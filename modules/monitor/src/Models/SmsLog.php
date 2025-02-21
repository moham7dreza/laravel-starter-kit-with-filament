<?php

namespace Modules\Monitor\Models;

use Modules\Monitor\Enums\SmsMessageTypeEnum;
use Modules\Monitor\Enums\SmsProviderEnum;
use Modules\Monitor\Enums\SmsStatusEnum;
use Modules\Monitor\Enums\SmsTypeEnum;
use MongoDB\Laravel\Eloquent\Model;

class SmsLog extends Model
{
    protected $collection = 'sms_logs';

    protected $connection = 'mongodb';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'connector' => SmsProviderEnum::class,
            'status' => SmsStatusEnum::class,
            'type' => SmsTypeEnum::class,
            'message_type' => SmsMessageTypeEnum::class,
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
