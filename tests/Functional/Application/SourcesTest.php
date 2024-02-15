<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSourcesTest;

class SourcesTest extends AbstractSourcesTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
