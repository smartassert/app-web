<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\Request;

readonly class TargetMapper
{
    /**
     * @var Target[]
     */
    private array $targets;

    /**
     * @param array<mixed> $targetDataCollection
     */
    public function __construct(
        array $targetDataCollection,
    ) {
        $targets = [];

        foreach ($targetDataCollection as $targetData) {
            if (is_array($targetData)) {
                $target = $this->createTarget($targetData);

                if ($target instanceof Target) {
                    $targets[] = $target;
                }
            }
        }

        $this->targets = $targets;
    }

    public function getForRequest(Request $request): ?string
    {
        if (!$request->attributes->has('_route')) {
            return null;
        }

        $source = $request->attributes->getString('_route');
        foreach ($this->targets as $target) {
            $name = $target->getName($source);

            if (is_string($name)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $data
     */
    private function createTarget(array $data): ?Target
    {
        $name = $data['name'] ?? '';
        $name = is_string($name) ? trim($name) : '';

        $sources = $data['sources'] ?? [];
        $sources = is_array($sources) ? $sources : [];

        return '' === $name ? null : new Target($name, $sources);
    }
}
