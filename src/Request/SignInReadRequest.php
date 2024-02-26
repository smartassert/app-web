<?php

declare(strict_types=1);

namespace App\Request;

use App\RedirectRoute\RedirectRoute;

readonly class SignInReadRequest
{
    public function __construct(
        public ?string $email,
        public RedirectRoute $route,
    ) {
    }
}
