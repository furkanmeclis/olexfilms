<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case CENTER_STAFF = 'center_staff';
    case DEALER_OWNER = 'dealer_owner';
    case DEALER_STAFF = 'dealer_staff';
}

