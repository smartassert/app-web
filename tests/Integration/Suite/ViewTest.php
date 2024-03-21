<?php

declare(strict_types=1);

namespace App\Tests\Integration\Suite;

use App\Tests\Application\Suite\AbstractViewTest;
use App\Tests\Integration\GetClientAdapterTrait;
use App\Tests\Integration\GetSessionIdentifierTrait;

class ViewTest extends AbstractViewTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
