<?php

declare(strict_types=1);

namespace App\Tests\Integration\Job;

use App\Tests\Application\Job\AbstractCreateTest;
use App\Tests\Integration\GetClientAdapterTrait;
use App\Tests\Integration\GetSessionIdentifierTrait;

class CreateTest extends AbstractCreateTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
