<?php

declare(strict_types=1);

namespace App\Tests\Services\EntityFactory;

use App\Tests\Services\ApplicationClient\Client;

readonly class SuiteFactory
{
    /**
     * @param string[] $tests
     */
    public function create(Client $client, string $sourceId, string $label, array $tests): string
    {
        $createSuiteResponse = $client->makeCreateSuiteRequest($sourceId, $label, $tests);
        if (302 !== $createSuiteResponse->getStatusCode()) {
            throw new \RuntimeException(
                'SuiteFactory::create() create file source failure',
                $createSuiteResponse->getStatusCode()
            );
        }

        $suitesResponse = $client->makeSuitesReadRequest();
        if (200 !== $suitesResponse->getStatusCode()) {
            throw new \RuntimeException(
                'FileSourceFactory::create() get sources failure',
                $createSuiteResponse->getStatusCode()
            );
        }

        $suitesBody = $suitesResponse->getBody()->getContents();

        $suiteUrls = [];
        preg_match('#/suite/[^"]+#', $suitesBody, $suiteUrls);

        $suiteUrl = $suiteUrls[0];

        return str_replace('/suite/', '', $suiteUrl);
    }
}
