<?php

namespace App\Enums;

enum UserRole: string
{
    case STO = 'STO';
    case BE = 'BE';
    case OIM = 'OIM';
    case RM = 'RM';
    case Management = 'Management';
    case Administrator = 'Administrator';
}
