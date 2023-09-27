<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Security\UserCredentials;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UserCredentialsResolver implements ValueResolverInterface
{
    /**
     * @return UserCredentials[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (UserCredentials::class !== $argument->getType()) {
            return [];
        }

        $userIdentifier = $request->request->get('user-identifier');
        if (!is_string($userIdentifier) || '' === $userIdentifier) {
            $userIdentifier = null;
        }

        $password = $request->request->get('password');
        if (!is_string($password) || '' === $password) {
            $password = null;
        }

        return [new UserCredentials($userIdentifier, $password)];
    }
}
