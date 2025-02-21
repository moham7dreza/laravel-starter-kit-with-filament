<?php

use Amiriun\SMS\SMSServiceProvider;
use Tests\TestsServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    SMSServiceProvider::class,
    TestsServiceProvider::class,
];
