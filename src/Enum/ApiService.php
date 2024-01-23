<?php

declare(strict_types=1);

namespace App\Enum;

enum ApiService: string
{
    case SOURCES = 'sources';
    case USERS = 'users';
}
