<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Job;

use App\Tests\Application\Job\AbstractCreateTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;
use App\Tests\Functional\Application\GetSessionIdentifierTrait;

class CreateTest extends AbstractCreateTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
