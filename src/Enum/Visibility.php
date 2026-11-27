<?php

namespace App\Enum;

enum Visibility : string
{
    case PRIVATE = 'private';
    case ORGANIZATION = 'organization';
    case GLOBAL = 'global';
}
