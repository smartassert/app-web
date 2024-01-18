<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClientFactory
{
    public function create(ClientInterface $client): Client
    {
        return new Client($client);
    }
}
