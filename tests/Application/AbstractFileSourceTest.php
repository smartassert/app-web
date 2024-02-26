<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\CookieExtractor;
use App\Tests\Services\CredentialsStore;
use App\Tests\Services\DataRepository;

abstract class AbstractFileSourceTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $credentialsStore = self::getContainer()->get(CredentialsStore::class);
        \assert($credentialsStore instanceof CredentialsStore);

        $cookieExtractor = self::getContainer()->get(CookieExtractor::class);
        \assert($cookieExtractor instanceof CookieExtractor);

        $credentialsStore->create($this->applicationClient, $this->getSessionIdentifier());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentialsStore->get());
        $credentialsStore->refresh(
            $sourcesResponse,
            $this->getSessionIdentifier(),
            $cookieExtractor->extract($sourcesResponse, $this->getSessionIdentifier())
        );

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest(
            $credentialsStore->get(),
            $label
        );
        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentialsStore->get());
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];
        $fileSourceId = str_replace('/sources/file/', '', $fileSourceUrl);

        $fileSourceReadResponse = $this->applicationClient->makeFileSourceReadRequest(
            $credentialsStore->get(),
            $fileSourceId
        );
        self::assertSame(200, $fileSourceReadResponse->getStatusCode());

        self::assertStringContainsString(
            '<title>File source "' . $label . '"</title>',
            $fileSourceReadResponse->getBody()->getContents()
        );
    }
}
