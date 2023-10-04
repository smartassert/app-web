<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\SignInErrorState;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BadCredentialsException extends AuthenticationException implements SignInExceptionInterface
{
    /**
     * @param non-empty-string $userIdentifier
     */
    public function __construct(
        private readonly string $userIdentifier
    ) {
        parent::__construct();
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getErrorState(): SignInErrorState
    {
        return SignInErrorState::UNAUTHORIZED;
    }
}
