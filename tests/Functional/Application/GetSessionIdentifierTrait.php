<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

trait GetSessionIdentifierTrait
{
    protected function getSessionIdentifier(): string
    {
        return 'MOCKSESSID';
    }
}
