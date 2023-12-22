<?php

declare(strict_types=1);

namespace App\RefreshableToken;

use SmartAssert\ApiClient\Data\User\Token;

class Serializer
{
    public function serialize(Token $token): string
    {
        return (string) json_encode(['token' => $token->token, 'refresh_token' => $token->refreshToken]);
    }

    public function deserialize(string $source): ?Token
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

        return new Token($token, $refreshToken);
    }
}
