<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\DataRepository\SourcesRepository;

abstract class AbstractFileSourceFileTest extends AbstractApplicationTestCase
{
    public function testCreateSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest($label);

        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest();
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];
        $fileSourceId = str_replace('/sources/file/', '', $fileSourceUrl);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $createFileSourceFileResponse = $this->applicationClient->makeFileSourceFileCreateRequest(
            $fileSourceId,
            $filename,
            $content
        );

        self::assertSame(302, $createFileSourceFileResponse->getStatusCode());
        self::assertSame($fileSourceUrl, $createFileSourceFileResponse->getHeaderLine('location'));
    }
}
