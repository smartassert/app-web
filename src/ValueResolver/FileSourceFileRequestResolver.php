<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\UnresolvableFileSourceRequestException;
use App\Request\FileSourceFileRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class FileSourceFileRequestResolver implements ValueResolverInterface
{
    /**
     * @return FileSourceFileRequest[]
     *
     * @throws UnresolvableFileSourceRequestException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (FileSourceFileRequest::class !== $argument->getType()) {
            return [];
        }

        $sourceId = $request->attributes->getString('id');
        if ('' === $sourceId) {
            throw new UnresolvableFileSourceRequestException($request);
        }

        $filename = $request->attributes->getString('filename');
        if ('' === $filename) {
            $filename = $request->request->getString('filename');
        }

        $content = $request->request->getString('content');

        return [new FileSourceFileRequest($sourceId, $filename, $content)];
    }
}
