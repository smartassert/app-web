<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\RefreshableToken\Encrypter;
use App\Security\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ResponseUserTokenSetter implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private Encrypter $tokenEncrypter,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['setUserTokenInResponse', 0],
            ],
        ];
    }

    public function setUserTokenInResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $response->headers->setCookie(Cookie::create(
                'token',
                $this->tokenEncrypter->encrypt($user->getSecurityToken())
            ));
        }

        $event->setResponse($response);
    }
}
