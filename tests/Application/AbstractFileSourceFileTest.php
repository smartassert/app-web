<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\FileSourceFileFactory;

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

    public function testViewSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->create($this->applicationClient, md5((string) rand()));
        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $fileSourceFileFactory = self::getContainer()->get(FileSourceFileFactory::class);
        \assert($fileSourceFileFactory instanceof FileSourceFileFactory);

        $fileSourceFileFactory->create($this->applicationClient, $fileSourceId, $filename, $content);

        $viewFileSourceFileResponse = $this->applicationClient->makeFileSourceFileViewRequest($fileSourceId, $filename);
        self::assertSame(200, $viewFileSourceFileResponse->getStatusCode());

        self::assertMatchesRegularExpression(
            '#<textarea[.\s\S]*>' . $content . '</textarea>#',
            $viewFileSourceFileResponse->getBody()->getContents()
        );
    }

    public function testUpdateSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->create($this->applicationClient, md5((string) rand()));
        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $fileSourceFileFactory = self::getContainer()->get(FileSourceFileFactory::class);
        \assert($fileSourceFileFactory instanceof FileSourceFileFactory);

        $fileSourceFileFactory->create($this->applicationClient, $fileSourceId, $filename, $content);

        $updatedContent = md5((string) rand());
        $updateFileSourceFileResponse = $this->applicationClient->makeFileSourceFileUpdateRequest(
            $fileSourceId,
            $filename,
            $updatedContent,
        );

        self::assertSame(302, $updateFileSourceFileResponse->getStatusCode());

        $viewFileSourceFileResponse = $this->applicationClient->makeFileSourceFileViewRequest($fileSourceId, $filename);
        self::assertSame(200, $viewFileSourceFileResponse->getStatusCode());

        $responseContent = $viewFileSourceFileResponse->getBody()->getContents();

        self::assertDoesNotMatchRegularExpression(
            '#<textarea[.\s\S]*>' . $content . '</textarea>#',
            $responseContent
        );

        self::assertMatchesRegularExpression(
            '#<textarea[.\s\S]*>' . $updatedContent . '</textarea>#',
            $responseContent
        );
    }
}
