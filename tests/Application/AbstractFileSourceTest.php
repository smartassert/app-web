<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\DataRepository\SourcesRepository;

abstract class AbstractFileSourceTest extends AbstractApplicationTestCase
{
    public function testGetSuccess(): void
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

        $fileSourceReadResponse = $this->applicationClient->makeFileSourceReadRequest($fileSourceId);
        self::assertSame(200, $fileSourceReadResponse->getStatusCode());

        self::assertStringContainsString(
            '<title>File source "' . $label . '"</title>',
            $fileSourceReadResponse->getBody()->getContents()
        );
    }
}
