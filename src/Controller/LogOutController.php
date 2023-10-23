<?php

declare(strict_types=1);

namespace App\Controller;

use App\SignInRedirectResponse\Factory;
use Symfony\Component\HttpFoundation\Response;

readonly class LogOutController
{
    public function __construct(
        private Factory $signInRedirectResponseFactory,
    ) {
    }

    public function handle(): Response
    {
        return $this->signInRedirectResponseFactory->create(
            userIdentifier: null,
            route: null,
        );
    }
}
