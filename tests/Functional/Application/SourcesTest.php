<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Enum\Routes;
use App\Tests\Application\AbstractSourcesTest;
use App\Tests\Services\DataRepository;
use App\Tests\Services\RequestCookieFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SourcesTest extends AbstractSourcesTest
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testAddFileSourceSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $requestCookieFactory = self::getContainer()->get(RequestCookieFactory::class);
        \assert($requestCookieFactory instanceof RequestCookieFactory);

        $requestCookie = $requestCookieFactory->create($this->applicationClient, $this->getSessionIdentifier());

        $sourcesUrl = $urlGenerator->generate(Routes::SOURCES_NAME->value);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $sourcesUrl,
            server: [
                'HTTP_COOKIE' => $requestCookie,
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $sourcesList = $crawler->filter('#sources_list');
        self::assertSame(0, $sourcesList->count());

        $label = md5((string) rand());

        $addFileSourceForm = $crawler->filter('#file_source_add input[type=submit]')->form([
            'label' => $label,
        ]);

        $this->kernelBrowser->submit($addFileSourceForm);

        $response = $this->kernelBrowser->getResponse();
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($sourcesUrl, $response->headers->get('location'));

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: $sourcesUrl,
            server: [
                'HTTP_COOKIE' => $requestCookie,
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $sourcesList = $crawler->filter('#sources_list');
        self::assertSame(1, $sourcesList->count());
        self::assertSame('sources_list', $sourcesList->attr('id'));

        $sourceItems = $sourcesList->filter('li');
        self::assertSame(1, $sourceItems->count());

        $source = $sourceItems->first();
        self::assertSame('file ' . $label, $source->text());
    }
}
