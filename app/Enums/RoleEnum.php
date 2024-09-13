<?php

namespace App\Enums;

use App\Traits\EnumDataListTrait;

enum RoleEnum: string
{
    use EnumDataListTrait;

    case super_admin = 'role super admin';
    case super_admin2 = 'super_admin';
    case panel_user = 'role panel user';
}
