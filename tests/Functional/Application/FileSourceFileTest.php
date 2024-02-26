<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractFileSourceFileTest;
use App\Tests\Services\CookieExtractor;
use App\Tests\Services\CredentialsStore;
use App\Tests\Services\DataRepository;

class FileSourceFileTest extends AbstractFileSourceFileTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

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
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $credentialsStore = self::getContainer()->get(CredentialsStore::class);
        \assert($credentialsStore instanceof CredentialsStore);

        $cookieExtractor = self::getContainer()->get(CookieExtractor::class);
        \assert($cookieExtractor instanceof CookieExtractor);

        $credentialsStore->create($this->applicationClient, $this->getSessionIdentifier());

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest($credentialsStore->get(), $label);
        $credentialsStore->refresh(
            $addFileSourceResponse,
            $this->getSessionIdentifier(),
            $cookieExtractor->extract($addFileSourceResponse, $this->getSessionIdentifier())
        );

        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentialsStore->get());
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $fileSourceUrl,
            server: [
                'HTTP_COOKIE' => $credentialsStore->get(),
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
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($fileSourceUrl, $response->headers->get('location'));

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $fileSourceUrl,
            server: [
                'HTTP_COOKIE' => $credentialsStore->get(),
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
}
