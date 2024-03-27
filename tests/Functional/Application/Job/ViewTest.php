<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Job;

use App\Tests\Application\Job\AbstractViewTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;
use App\Tests\Functional\Application\GetSessionIdentifierTrait;

class ViewTest extends AbstractViewTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
