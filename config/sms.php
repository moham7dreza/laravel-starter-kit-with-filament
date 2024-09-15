<?php

use Amiriun\Sms\Events\SMSWasDelivered;
use Amiriun\Sms\Events\SMSWasReceived;
use Amiriun\SMS\Repositories\Storage\MysqlStorage;
use Amiriun\SMS\Services\Drivers\DebugDriver;
use Amiriun\SMS\Services\Drivers\KavenegarDriver;
use Amiriun\SMS\Services\Drivers\PayamResanDriver;
use Amiriun\SMS\Services\Drivers\SmsIrDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Gateway
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, sms messages should not be send to endusers.
    | for handle this issue you can use "debug" gateway to log messages instead of send to users.
    | supported: debug , kavenegar, sms_ir, payamresan
    |
    */
    'default_gateway' => env('SMS_GATEWAY', 'debug'),

    'logging' => [
        'storage' => MysqlStorage::class,
        'table_name' => 'sms_logs',
        'send_logs' => [
            'need_log' => true,
        ],
        'receive_logs' => [
            'need_log' => true,
        ],
    ],

    'kavenegar' => [
        'api_key' => env('KAVENEGAR_API_KEY', 'YOUR_API_KEY'),
        'numbers' => [
            'YOUR_NUMBERS',
        ],
    ],
    'mediana' => [
        'api_key' => env('MEDIANA_API_KEY', 'YOUR_API_KEY'),
        'numbers' => [
            'YOUR_NUMBERS',
        ],
    ],
    'mellipayamak' => [
        'api_key' => env('MELLIPAYAMAK_API_KEY', 'YOUR_API_KEY'),
        'numbers' => [
            'YOUR_NUMBERS',
        ],
    ],
    'sms_ir' => [
        'api_key' => env('SMSIR_API_KEY', 'YOUR_API_KEY'),
        'secret_key' => env('SMSIR_SECRET_KEY', 'YOUR_SECRET_KEY'),
        'numbers' => [
            'YOUR_NUMBERS',
        ],
    ],
    'payamresan' => [
        'username' => env('PAYAMRESAN_USERNAME', 'YOUR_USERNAME'),
        'password' => env('PAYAMRESAN_PASSWORD', 'YOUR_PASSWORD'),
        'numbers' => [
            'YOUR_NUMBERS',
        ],
    ],
    'debug' => [
        'numbers' => [
            '30003000300',
        ],
    ],

    'map_gateway_to_connector' => [
        'debug' => DebugDriver::class,
        'kavenegar' => KavenegarDriver::class,
        'sms_ir' => SmsIrDriver::class,
        'payamresan' => PayamResanDriver::class,
    ],

    'events' => [
        'after_receiving_sms' => SMSWasReceived::class,
        'after_delivering_sms' => SMSWasDelivered::class,
    ]
];
