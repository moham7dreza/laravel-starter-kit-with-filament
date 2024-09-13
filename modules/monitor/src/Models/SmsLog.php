<?php

namespace Modules\Monitor\Models;

use MongoDB\Laravel\Eloquent\Model;

class SmsLog extends Model
{
    protected $collection = 'sms_logs';

    protected $connection = 'mongodb';

    protected $guarded = [];
}
