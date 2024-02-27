<?php

declare(strict_types=1);

namespace App\Tests\Services\EntityFactory;

use App\Tests\Services\ApplicationClient\Client;

readonly class FileSourceFactory
{
    public function create(Client $client, string $label): string
    {
        $addFileSourceResponse = $client->makeFileSourceAddRequest($label);
        if (302 !== $addFileSourceResponse->getStatusCode()) {
            throw new \RuntimeException(
                'FileSourceFactory::create() create file source failure',
                $addFileSourceResponse->getStatusCode()
            );
        }

        $sourcesResponse = $client->makeSourcesReadRequest();
        if (200 !== $sourcesResponse->getStatusCode()) {
            throw new \RuntimeException(
                'FileSourceFactory::create() get sources failure',
                $addFileSourceResponse->{$sourcesResponse}()
            );
        }

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];

        return str_replace('/sources/file/', '', $fileSourceUrl);
    }
}
