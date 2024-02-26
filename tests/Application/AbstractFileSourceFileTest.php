<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\CookieExtractor;
use App\Tests\Services\Credentials;
use App\Tests\Services\DataRepository;

abstract class AbstractFileSourceFileTest extends AbstractApplicationTestCase
{
    public function testCreateSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $credentials = self::getContainer()->get(Credentials::class);
        \assert($credentials instanceof Credentials);

        $cookieExtractor = self::getContainer()->get(CookieExtractor::class);
        \assert($cookieExtractor instanceof CookieExtractor);

        $credentials->create($this->applicationClient, $this->getSessionIdentifier());

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest($credentials, $label);

        $credentials->refresh($addFileSourceResponse, $this->getSessionIdentifier());

        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentials);
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];
        $fileSourceId = str_replace('/sources/file/', '', $fileSourceUrl);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $createFileSourceFileResponse = $this->applicationClient->makeFileSourceFileCreateRequest(
            $credentials,
            $fileSourceId,
            $filename,
            $content
        );

        self::assertSame(302, $createFileSourceFileResponse->getStatusCode());
        self::assertSame($fileSourceUrl, $createFileSourceFileResponse->getHeaderLine('location'));
    }
}
