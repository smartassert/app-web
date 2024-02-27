<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;

abstract class AbstractFileSourceFileTest extends AbstractApplicationTestCase
{
    public function testCreateSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->create($this->applicationClient, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $response = $this->applicationClient->makeFileSourceFileCreateRequest($fileSourceId, $filename, $content);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/sources/file/' . $fileSourceId, $response->getHeaderLine('location'));
    }
}
