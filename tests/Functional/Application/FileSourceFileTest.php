<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractFileSourceFileTest;
use App\Tests\Assertions\SymfonyRedirectResponseAssertionTrait;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\FileSourceFileFactory;

class FileSourceFileTest extends AbstractFileSourceFileTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
    use SymfonyRedirectResponseAssertionTrait;

    /**
     * @dataProvider createFileSourceFileBadRequestDataProvider
     */
    public function testCreateFileSourceFileBadRequest(
        string $filename,
        string $content,
        string $expectedErrorMessage,
        bool $expectedFilenameHasError,
        bool $expectedContentHasError,
    ): void {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);
        $fileSourceUrl = '/sources/file/' . $fileSourceId;

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $fileSourceUrl,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        $filesList = $crawler->filter('#files_list');
        self::assertSame(0, $filesList->count());

        $addFileSourceFileForm = $crawler->filter('#file_source_file_add input[type=submit]')->form([
            'filename' => $filename,
            'content' => $content,
        ]);

        $this->kernelBrowser->submit($addFileSourceFileForm);

        $response = $this->kernelBrowser->getResponse();
        $this->assertSymfonyRedirectResponse($response, $fileSourceUrl);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $fileSourceUrl,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $filesList = $crawler->filter('#files_list');
        self::assertSame(0, $filesList->count());

        $formElement = $crawler->filter('#file_source_file_add');
        self::assertSame('error', $formElement->attr('class'));

        $errorContainer = $formElement->filter('span.error');
        self::assertSame(1, $errorContainer->count());
        self::assertSame($expectedErrorMessage, $errorContainer->innerText());

        $filenameLabel = $formElement->filter('[for=file_source_file_add_filename]');
        self::assertSame($expectedFilenameHasError, 'error' === $filenameLabel->attr('class'));

        $filenameField = $formElement->filter('#file_source_file_add_filename');
        self::assertSame($expectedFilenameHasError, 'error' === $filenameField->attr('class'));
        self::assertSame($filename, $filenameField->attr('value'));

        $contentLabel = $formElement->filter('[for=file_source_file_add_content]');
        self::assertSame($expectedContentHasError, 'error' === $contentLabel->attr('class'));

        $contentField = $formElement->filter('#file_source_file_add_content');
        self::assertSame($expectedContentHasError, 'error' === $contentField->attr('class'));
        self::assertSame($content, $contentField->html());
    }

    /**
     * @return array<mixed>
     */
    public function createFileSourceFileBadRequestDataProvider(): array
    {
        return [
            'filename empty' => [
                'filename' => '',
                'content' => md5((string) rand()),
                'expectedErrorMessage' => 'This value is invalid. It must be a valid "yaml_filename".',
                'expectedFilenameHasError' => true,
                'expectedContentHasError' => false,
            ],
            'content not valid yaml' => [
                'filename' => md5((string) rand()) . '.yaml',
                'content' => "-\n.",
                'expectedErrorMessage' => 'This value is invalid. It must be a valid "yaml".',
                'expectedFilenameHasError' => false,
                'expectedContentHasError' => true,
            ],
        ];
    }

    public function testViewContentSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);
        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $fileSourceFileFactory = self::getContainer()->get(FileSourceFileFactory::class);
        \assert($fileSourceFileFactory instanceof FileSourceFileFactory);

        $fileSourceFileFactory->create($this->applicationClient, $fileSourceId, $filename, $content);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/sources/file/' . $fileSourceId . '/' . $filename,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $modifyHeading = $crawler->filter('h3');
        self::assertSame('Modify "' . $filename . '"', $modifyHeading->text());

        $contentField = $crawler->filter('textarea');
        self::assertSame($content, $contentField->html());
    }
}
