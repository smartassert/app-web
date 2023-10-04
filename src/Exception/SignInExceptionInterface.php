<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\SignInErrorState;

interface SignInExceptionInterface
{
    /**
     * @return ?non-empty-string
     */
    public function getUserIdentifier(): ?string;

    public function getErrorState(): SignInErrorState;
}
