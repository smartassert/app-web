<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
readonly class UserProvider implements UserProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private RequestTokenExtractor $tokenExtractor,
        private HttpMessageFactoryInterface $psrHttpFactory,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $identifier = trim($identifier);
        if ('' === $identifier) {
            throw new UserNotFoundException();
        }

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            throw new UserNotFoundException();
        }

        $securityToken = $this->tokenExtractor->extract(
            $this->psrHttpFactory->createRequest($currentRequest)
        );

        if (null === $securityToken) {
            throw new UserNotFoundException();
        }

        return new User($identifier, $securityToken);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
