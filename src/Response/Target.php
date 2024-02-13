<?php

declare(strict_types=1);

namespace App\Response;

readonly class Target
{
    /**
     * @var non-empty-string[]
     */
    private array $sources;

    /**
     * @param non-empty-string   $name
     * @param non-empty-string[] $sources
     */
    public function __construct(
        private string $name,
        array $sources,
    ) {
        $filteredSources = [];

        foreach ($sources as $source) {
            $source = trim($source);

            if ('' !== $source) {
                $filteredSources[] = $source;
            }
        }

        $this->sources = $filteredSources;
    }

    /**
     * @return ?non-empty-string
     */
    public function getName(string $source): ?string
    {
        if (in_array($source, $this->sources)) {
            return $this->name;
        }

        return null;
    }
}
