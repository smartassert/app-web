<?php

declare(strict_types=1);

namespace App\Tests\Services\EntityFactory;

use App\Tests\Services\ApplicationClient\Client;

readonly class JobFactory
{
    /**
     * @return non-empty-string
     */
    public function create(Client $client, string $suiteId): string
    {
        $response = $client->makeCreateJobRequest($suiteId);
        if (302 !== $response->getStatusCode()) {
            throw new \RuntimeException(
                'JobFactory::create() create job failure',
                $response->getStatusCode()
            );
        }

        $jobId = str_replace('/job/', '', $response->getHeaderLine('location'));
        \assert('' !== $jobId);

        return $jobId;
    }
}
