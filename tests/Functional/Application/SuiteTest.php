<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;

class SuiteTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;

    public function testAddSuiteSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceLabel = md5((string) rand());
        $fileSourceId = $fileSourceFactory->create($this->applicationClient, $fileSourceLabel);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suites',
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $suitesList = $crawler->filter('#suites_list');
        self::assertSame(0, $suitesList->count());

        $suiteLabel = md5((string) rand());
        $suiteTests = 'test1.yaml' . "\n" . 'test2.yaml' . "\n" . 'test3.yaml';

        $addSuiteForm = $crawler->filter('#suite_add input[type=submit]')->form([
            'label' => $suiteLabel,
            'source_id' => $fileSourceId,
            'tests' => $suiteTests,
        ]);

        $this->kernelBrowser->submit($addSuiteForm);

        $response = $this->kernelBrowser->getResponse();
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/suites', $response->headers->get('location'));

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suites',
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $suitesList = $crawler->filter('#suites_list');
        self::assertSame(1, $suitesList->count());
        self::assertSame('suites_list', $suitesList->attr('id'));

        $suiteItems = $suitesList->filter('li');
        self::assertSame(1, $suiteItems->count());

        $suite = $suiteItems->first();
        self::assertSame($suiteLabel, $suite->text());
    }
}
