<?php

declare(strict_types=1);

namespace App\Tests\Assertions;

use Symfony\Component\HttpFoundation\Response;

trait SymfonyRedirectResponseAssertionTrait
{
    public function assertSymfonyRedirectResponse(Response $response, string $expected): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($expected, $response->headers->get('location'));
    }
}
