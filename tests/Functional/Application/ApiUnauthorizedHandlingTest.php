<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractApiUnauthorizedHandlingTest;

class ApiUnauthorizedHandlingTest extends AbstractApiUnauthorizedHandlingTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
