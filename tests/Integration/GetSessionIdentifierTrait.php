<?php

declare(strict_types=1);

namespace App\Tests\Integration;

trait GetSessionIdentifierTrait
{
    protected function getSessionIdentifier(): string
    {
        return 'id';
    }
}
