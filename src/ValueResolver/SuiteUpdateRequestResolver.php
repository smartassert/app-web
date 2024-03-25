<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Request\SuiteUpdateRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class SuiteUpdateRequestResolver implements ValueResolverInterface
{
    /**
     * @return SuiteUpdateRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (SuiteUpdateRequest::class !== $argument->getType()) {
            return [];
        }

        $id = $request->attributes->getString('id');
        $label = $request->request->getString('label');
        $sourceId = $request->request->getString('source_id');

        $serializedTests = $request->request->getString('tests');
        $tests = explode("\n", $serializedTests);

        return [new SuiteUpdateRequest($id, $label, $sourceId, $tests)];
    }
}
