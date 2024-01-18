<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use SmartAssert\SymfonyTestClient\ClientInterface;

class ClientFactory
{
    public function create(ClientInterface $client): Client
    {
        return new Client($client);
    }
}
