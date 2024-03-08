<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSuitesTest;

class SuitesTest extends AbstractSuitesTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
