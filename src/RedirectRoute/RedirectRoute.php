<?php

declare(strict_types=1);

namespace App\RedirectRoute;

readonly class RedirectRoute
{
    /**
     * @param array<string, int|string> $parameters
     */
    public function __construct(
        private ?string $name,
        private array $parameters,
    ) {
    }

    public function serialize(): string
    {
        return base64_encode((string) json_encode(['name' => $this->name, 'parameters' => $this->parameters]));
    }

    public static function deserialize(string $serialized): ?self
    {
        $decodedJson = base64_decode($serialized, true);
        if (false === $decodedJson) {
            return null;
        }

        $data = json_decode($decodedJson, true);
        if (!is_array($data)) {
            return null;
        }

        $name = $data['name'] ?? null;
        $name = is_string($name) ? $name : null;

        $sourceParameters = $data['parameters'] ?? [];
        $sourceParameters = is_array($sourceParameters) ? $sourceParameters : [];

        $parameters = [];
        foreach ($sourceParameters as $key => $value) {
            if (is_string($key) && (is_string($value) || is_int($value))) {
                $parameters[$key] = $value;
            }
        }

        return new RedirectRoute($name, $parameters);
    }
}
