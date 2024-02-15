<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractFileSourceTest;

class FileSourceTest extends AbstractFileSourceTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
