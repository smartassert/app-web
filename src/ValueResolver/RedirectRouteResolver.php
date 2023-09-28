<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\RedirectRoute\Factory;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class RedirectRouteResolver implements ValueResolverInterface
{
    public function __construct(
        private Factory $factory,
        private Serializer $serializer,
    ) {
    }

    /**
     * @return RedirectRoute[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (RedirectRoute::class !== $argument->getType()) {
            return [];
        }

        $serializedRedirectRoute = $request->request->get('route');
        if (!is_string($serializedRedirectRoute)) {
            return [$this->factory->getDefault()];
        }

        return [$this->serializer->deserialize($serializedRedirectRoute)];
    }
}
