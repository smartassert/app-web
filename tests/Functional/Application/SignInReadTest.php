<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInReadTest;
use Symfony\Component\Routing\RouterInterface;

class SignInReadTest extends AbstractSignInReadTest
{
    use GetClientAdapterTrait;

    public function testReadRendersUserIdentifier(): void
    {
        $router = self::getContainer()->get(RouterInterface::class);
        \assert($router instanceof RouterInterface);

        $email = md5((string) rand()) . '@example.com';

        $crawler = self::$kernelBrowser->request('GET', $router->generate('sign_in_view', ['email' => $email]));

        $renderedEmail = $crawler->filter('input[name=user-identifier]')->attr('value');

        self::assertSame($email, $renderedEmail);
    }
}
