<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\RefreshableToken\Encrypter;
use App\Response\RedirectResponseFactory;
use App\Security\User;
use SmartAssert\ApiClient\UsersClient;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LogoutEvent;

readonly class UserTokenRevoker implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UsersClient $usersClient,
        private Encrypter $tokenEncrypter,
        private RedirectResponseFactory $redirectResponseFactory,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => [
                ['revoke', 0],
            ],
        ];
    }

    public function revoke(): void
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            try {
                $this->usersClient->revokeRefreshToken(
                    $user->getSecurityToken()->token,
                    $user->getSecurityToken()->refreshToken
                );
            } catch (\Throwable) {
                // Intentionally ignore all exceptions
            }
        }
    }
}
