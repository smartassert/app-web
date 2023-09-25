<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

readonly class User implements UserInterface
{
    /**
     * @param non-empty-string $identifier
     * @param non-empty-string $securityToken
     */
    public function __construct(
        private string $identifier,
        private string $securityToken,
    ) {
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return non-empty-string
     */
    public function getSecurityToken(): string
    {
        return $this->securityToken;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [
            'ROLE_USER',
        ];
    }

    public function eraseCredentials(): void
    {
    }
}
