<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractSignInReadTest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignInReadTest extends AbstractSignInReadTest
{
    use GetClientAdapterTrait;

    public function testReadRendersUserIdentifier(): void
    {
        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $email = md5((string) rand()) . '@example.com';

        $crawler = self::$kernelBrowser->request('GET', $urlGenerator->generate('sign_in_view', ['email' => $email]));

        $renderedEmail = $crawler->filter('input[name=user-identifier]')->attr('value');

        self::assertSame($email, $renderedEmail);
    }
}
