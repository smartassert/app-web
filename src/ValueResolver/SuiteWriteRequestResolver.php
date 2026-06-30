<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Request\SuiteWriteRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class SuiteWriteRequestResolver implements ValueResolverInterface
{
    /**
     * @return SuiteWriteRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (SuiteWriteRequest::class !== $argument->getType()) {
            return [];
        }

        $id = $request->attributes->getString('id');
        $label = $request->request->getString('label');
        $sourceId = $request->request->getString('source_id');

        $serializedTests = $request->request->getString('tests');
        $tests = explode("\n", $serializedTests);
        $tests = array_map(fn (string $test) => trim($test), $tests);

        return [new SuiteWriteRequest($id, $label, $sourceId, $tests)];
    }
}
