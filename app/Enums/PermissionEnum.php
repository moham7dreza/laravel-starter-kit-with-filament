<?php

namespace App\Enums;

use App\Traits\EnumDataListTrait;

enum PermissionEnum: string
{
    use EnumDataListTrait;

    case panel = 'panel';
}
