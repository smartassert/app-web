<?php

declare(strict_types=1);

namespace App\Enum;

enum SignInErrorState: string
{
    case EMAIL_EMPTY = 'email_empty';
    case PASSWORD_EMPTY = 'password_empty';
    case UNAUTHORIZED = 'unauthorized';

    public static function is(string $value): bool
    {
        foreach (SignInErrorState::cases() as $errorState) {
            if ($value === $errorState->value) {
                return true;
            }
        }

        return false;
    }
}
