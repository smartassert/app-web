<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Suite;

use App\Tests\Application\Suite\AbstractViewTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;
use App\Tests\Functional\Application\GetSessionIdentifierTrait;

class ViewTest extends AbstractViewTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
