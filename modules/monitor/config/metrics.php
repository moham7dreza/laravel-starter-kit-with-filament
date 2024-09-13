<?php

use Modules\Monitor\Enums\MetricTypeEnum as type;

return [
    'enabled' => env('METRICS_LOG_ENABLED', true),
    'cache-ttl' => env('METRICS_CACHE_TTL', 10),
    'limited-records-count' => env('METRICS_LIMITED_RECORDS_COUNT', 100),
    'monitoring-period' => env('METRICS_MONITORING_PERIOD', 5),
    'monitoring-sms-period' => env('METRICS_MONITORING_SMS_PERIOD', 1),
    'provider' => env('METRICS_PROVIDER', 'prometheus'),
    'productive_enabled' => env('METRICS_PRODUCTIVE_ENABLE', true),
    'gateways_enabled' => env('METRICS_GATEWAYS_ENABLE', false),
    'internal_services_enabled' => env('METRICS_INTERNAL_SERVICES_ENABLE', true),
    'sms_enabled' => env('METRICS_SMS_ENABLE', false),
    'request_enabled' => env('METRICS_REQUEST_ENABLE', false),
    'brear_token' => env('METRICS_BEARER_TOKEN'),

    'export' => [
        'productive' => [
            type::login->name => 'api_endpoints_internal_login',
            type::login_otp->name => 'api_endpoints_internal_login_otp',
            type::verify_otp->name => 'api_endpoints_internal_verify_otp',
            type::register->name => 'api_endpoints_internal_register',
            type::search->name => 'api_endpoints_internal_search',
            type::pdp->name => 'api_endpoints_internal_pdp',
        ],
        'payment_gateway' => 'api_endpoints_internal_payment',
        'sms' => 'api_endpoints_internal_sms',
    ],
    'gateways' => [

    ]
];
