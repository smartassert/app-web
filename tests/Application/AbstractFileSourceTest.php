<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\CookieExtractor;
use App\Tests\Services\CredentialsFactory;
use App\Tests\Services\DataRepository;

abstract class AbstractFileSourceTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $credentialsFactory = self::getContainer()->get(CredentialsFactory::class);
        \assert($credentialsFactory instanceof CredentialsFactory);

        $cookieExtractor = self::getContainer()->get(CookieExtractor::class);
        \assert($cookieExtractor instanceof CookieExtractor);

        $credentials = $credentialsFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentials);
        $credentials = $credentialsFactory->createFromResponse(
            $sourcesResponse,
            $this->getSessionIdentifier(),
            $cookieExtractor->extract($sourcesResponse, $this->getSessionIdentifier())
        );

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest($credentials, $label);
        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentials);
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];
        $fileSourceId = str_replace('/sources/file/', '', $fileSourceUrl);

        $fileSourceReadResponse = $this->applicationClient->makeFileSourceReadRequest($credentials, $fileSourceId);
        self::assertSame(200, $fileSourceReadResponse->getStatusCode());

        self::assertStringContainsString(
            '<title>File source "' . $label . '"</title>',
            $fileSourceReadResponse->getBody()->getContents()
        );
    }
}