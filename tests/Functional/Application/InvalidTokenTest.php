<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractInvalidTokenTest;

class InvalidTokenTest extends AbstractInvalidTokenTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
