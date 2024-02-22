<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractFileSourceFileTest;
use App\Tests\Services\CookieExtractor;
use App\Tests\Services\CredentialsFactory;
use App\Tests\Services\DataRepository;

class FileSourceFileTest extends AbstractFileSourceFileTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testAddFileSourceFileBadRequest(): void
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

        $label = md5((string) rand());
        $addFileSourceResponse = $this->applicationClient->makeFileSourceAddRequest($credentials, $label);
        $credentials = $credentialsFactory->createFromResponse(
            $addFileSourceResponse,
            $this->getSessionIdentifier(),
            $cookieExtractor->extract($addFileSourceResponse, $this->getSessionIdentifier())
        );

        self::assertSame(302, $addFileSourceResponse->getStatusCode());

        $sourcesResponse = $this->applicationClient->makeSourcesReadRequest($credentials);
        self::assertSame(200, $sourcesResponse->getStatusCode());

        $sourcesBody = $sourcesResponse->getBody()->getContents();

        $fileSourceUrls = [];
        preg_match('#/sources/file/[^"]+#', $sourcesBody, $fileSourceUrls);

        $fileSourceUrl = $fileSourceUrls[0];

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $fileSourceUrl,
            server: [
                'HTTP_COOKIE' => $credentials,
            ]
        );

        $filesList = $crawler->filter('#files_list');
        self::assertSame(0, $filesList->count());

        $filename = '';
        $content = md5((string) rand());

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
                'HTTP_COOKIE' => $credentials,
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $filesList = $crawler->filter('#files_list');
        self::assertSame(0, $filesList->count());

        $formElement = $crawler->filter('#file_source_file_add');
        self::assertSame('error', $formElement->attr('class'));

        $errorContainer = $formElement->filter('span.error');
        self::assertSame(1, $errorContainer->count());
        self::assertSame('This value is invalid. It must be a valid "yaml_filename".', $errorContainer->innerText());

        $formFieldLabel = $formElement->filter('[for=file_source_file_add_filename]');
        self::assertSame('error', $formFieldLabel->attr('class'));

        $formField = $formElement->filter('#file_source_file_add_filename');
        self::assertSame('error', $formField->attr('class'));
    }
}
