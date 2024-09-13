<?php

namespace App\Enums;

use App\Traits\EnumDataListTrait;

enum EnvironmentEnum: string
{
    use EnumDataListTrait;

    case production = 'production';
    case demo = 'demo';
    case testing = 'testing';
    case local = 'local';
}
