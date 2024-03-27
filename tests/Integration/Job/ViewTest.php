<?php

declare(strict_types=1);

namespace App\Tests\Integration\Job;

use App\Tests\Application\Job\AbstractViewTest;
use App\Tests\Integration\GetClientAdapterTrait;
use App\Tests\Integration\GetSessionIdentifierTrait;

class ViewTest extends AbstractViewTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
