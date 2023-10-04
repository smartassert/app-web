<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\SignInErrorState;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserIdentifierMissingException extends AuthenticationException implements SignInExceptionInterface
{
    public function getUserIdentifier(): null
    {
        return null;
    }

    public function getErrorState(): SignInErrorState
    {
        return SignInErrorState::EMAIL_EMPTY;
    }
}
