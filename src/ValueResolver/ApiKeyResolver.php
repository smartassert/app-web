<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\ApiKeyMissingException;
use App\Security\ApiKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class ApiKeyResolver implements ValueResolverInterface
{
    /**
     * @return ApiKey[]
     *
     * @throws ApiKeyMissingException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (ApiKey::class !== $argument->getType()) {
            return [];
        }

        $apiKey = $request->getSession()->get('api_key');
        if (!is_string($apiKey) || '' === $apiKey) {
            throw new ApiKeyMissingException();
        }

        return [new ApiKey($apiKey)];
    }
}
