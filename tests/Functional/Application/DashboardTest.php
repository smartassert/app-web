<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractDashboardTest;

class DashboardTest extends AbstractDashboardTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
}
