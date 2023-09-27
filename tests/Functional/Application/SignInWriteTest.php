<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInWriteTest;
use App\Tests\Services\SessionHandler;

class SignInWriteTest extends AbstractSignInWriteTest
{
    use GetClientAdapterTrait;

    public function testWriteEmptyUserIdentifier(): void
    {
        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $session = $sessionHandler->create();
        $sessionHandler->persist(self::$kernelBrowser, $session);

        self::$staticApplicationClient->makeSignInPageWriteRequest(null, null);

        $flashBag = $session->getFlashBag();

        self::assertSame(['empty-user-identifier'], $flashBag->get('error'));
    }
}
