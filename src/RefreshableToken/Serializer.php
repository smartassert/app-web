<?php

declare(strict_types=1);

namespace App\RefreshableToken;

use SmartAssert\ApiClient\Model\RefreshableToken;

class Serializer
{
    public function serialize(RefreshableToken $token): string
    {
        return (string) json_encode(['token' => $token->token, 'refresh_token' => $token->refreshToken]);
    }

    public function deserialize(string $source): ?RefreshableToken
    {
        $data = json_decode($source, true);
        if (!is_array($data)) {
            return null;
        }

        $token = $data['token'] ?? null;
        if (!is_string($token) || '' === $token) {
            return null;
        }

        $refreshToken = $data['refresh_token'] ?? null;
        if (!is_string($refreshToken) || '' === $refreshToken) {
            return null;
        }

        return new RefreshableToken($token, $refreshToken);
    }
}
