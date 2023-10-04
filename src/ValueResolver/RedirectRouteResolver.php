<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class RedirectRouteResolver implements ValueResolverInterface
{
    public function __construct(
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

        return [$this->serializer->deserialize($request->request->getString('route'))];
    }
}
