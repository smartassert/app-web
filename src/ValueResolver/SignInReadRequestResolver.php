<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\RedirectRoute\Serializer;
use App\Request\SignInReadRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class SignInReadRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private Serializer $serializer,
    ) {
    }

    /**
     * @return SignInReadRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (SignInReadRequest::class !== $argument->getType()) {
            return [];
        }

        $email = $request->query->getString('email');
        if ('' === $email) {
            $email = null;
        }

        $serializedRoute = $request->query->getString('route');
        $route = $this->serializer->deserialize($serializedRoute);

        return [new SignInReadRequest($email, $route)];
    }
}
