<?php

declare(strict_types=1);

namespace App\Security;

use SmartAssert\ApiClient\Data\User\Token;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class User implements UserInterface
{
    /**
     * @param non-empty-string $identifier
     */
    public function __construct(
        private string $identifier,
        private Token $securityToken,
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

    public function getSecurityToken(): Token
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
