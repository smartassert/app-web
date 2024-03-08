<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Request\SuiteCreateRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class SuiteCreateRequestResolver implements ValueResolverInterface
{
    /**
     * @return SuiteCreateRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (SuiteCreateRequest::class !== $argument->getType()) {
            return [];
        }

        $label = $request->request->getString('label');
        $sourceId = $request->request->getString('source_id');

        $serializedTests = $request->request->getString('tests');
        $tests = explode("\n", $serializedTests);

        return [new SuiteCreateRequest($label, $sourceId, $tests)];
    }
}
