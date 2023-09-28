<?php

declare(strict_types=1);

namespace App\RedirectRoute;

readonly class Serializer
{
    public function __construct(
        private Factory $factory,
    ) {
    }

    public function serialize(RedirectRoute $redirectRoute): string
    {
        return base64_encode((string) json_encode([
            'name' => $redirectRoute->name,
            'parameters' => $redirectRoute->parameters,
        ]));
    }

    public function deserialize(string $serialized): RedirectRoute
    {
        $decodedJson = base64_decode($serialized, true);
        if (false === $decodedJson) {
            return $this->factory->getDefault();
        }

        $data = json_decode($decodedJson, true);
        if (!is_array($data)) {
            return $this->factory->getDefault();
        }

        $name = $data['name'] ?? null;
        $name = is_string($name) ? $name : null;

        if (null === $name) {
            return $this->factory->getDefault();
        }

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
