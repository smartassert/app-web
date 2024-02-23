<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Request\FileSourceCreateRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class FileSourceCreateRequestResolver implements ValueResolverInterface
{
    /**
     * @return FileSourceCreateRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (FileSourceCreateRequest::class !== $argument->getType()) {
            return [];
        }

        $label = $request->request->getString('label');

        return [new FileSourceCreateRequest($label)];
    }
}
