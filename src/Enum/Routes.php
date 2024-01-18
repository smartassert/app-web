<?php

declare(strict_types=1);

namespace App\Enum;

enum Routes: string
{
    case SIGN_IN_VIEW_NAME = 'sign_in_view';
    case SIGN_IN_VIEW_PATH = '/sign-in/';
    case DASHBOARD_NAME = 'dashboard';
    case LOG_OUT_NAME = 'log_out_handle';

    case SOURCES_NAME = 'sources';
}
