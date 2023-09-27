<?php

declare(strict_types=1);

namespace App\Tests\Services;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactory as SymfonySessionFactory;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

readonly class SessionHandler
{
    public function __construct(
        private SymfonySessionFactory $sessionFactory,
    ) {
    }

    public function create(): FlashBagAwareSessionInterface
    {
        $session = $this->sessionFactory->createSession();
        \assert($session instanceof FlashBagAwareSessionInterface);

        $session->start();
        $session->save();

        return $session;
    }

    public function persist(KernelBrowser $client, SessionInterface $session): void
    {
        $sessionCookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($sessionCookie);
    }
}
