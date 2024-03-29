<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Assertions\SymfonyRedirectResponseAssertionTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;
use App\Tests\Functional\Application\GetSessionIdentifierTrait;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;

class CreateTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
    use SymfonyRedirectResponseAssertionTrait;

    public function testCreateSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);

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
        $this->assertSymfonyRedirectResponse($response, '/suites');

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

    public function testCreateBadRequest(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);

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

        $suiteLabel = str_repeat('.', 256);
        $suiteTests = 'test1.yaml';

        $formElement = $crawler->filter('#suite_add');
        self::assertNull($formElement->attr('class'));

        $errorContainer = $formElement->filter('span.error');
        self::assertSame(0, $errorContainer->count());

        $sourceIdLabel = $formElement->filter('[for=suite_add_source_id]');
        self::assertNull($sourceIdLabel->attr('class'));

        $sourceIdField = $formElement->filter('#suite_add_source_id');
        self::assertNull($sourceIdField->attr('class'));

        $sourceIdOptions = $sourceIdField->filter('option');
        foreach ($sourceIdOptions as $sourceIdOption) {
            self::assertInstanceOf(\DOMElement::class, $sourceIdOption);
            self::assertFalse($sourceIdOption->hasAttribute('selected'));
        }

        $labelLabel = $formElement->filter('[for=suite_add_label]');
        self::assertNull($labelLabel->attr('class'));

        $labelField = $formElement->filter('#suite_add_label');
        self::assertNull($labelField->attr('class'));
        self::assertEmpty($labelField->attr('value'));

        $testsLabel = $formElement->filter('[for=suite_add_tests]');
        self::assertNull($testsLabel->attr('class'));

        $testsField = $formElement->filter('#suite_add_tests');
        self::assertEmpty($testsField->html());

        $addSuiteForm = $crawler->filter('#suite_add input[type=submit]')->form([
            'label' => $suiteLabel,
            'source_id' => $fileSourceId,
            'tests' => $suiteTests,
        ]);
        $this->kernelBrowser->submit($addSuiteForm);

        $response = $this->kernelBrowser->getResponse();
        $this->assertSymfonyRedirectResponse($response, '/suites');

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

        $formElement = $crawler->filter('#suite_add');
        self::assertSame('error', $formElement->attr('class'));

        $errorContainer = $formElement->filter('span.error');
        self::assertSame(1, $errorContainer->count());
        self::assertSame('This value must be between 1 and 255 characters long.', $errorContainer->innerText());

        $sourceIdLabel = $formElement->filter('[for=suite_add_source_id]');
        self::assertNull($sourceIdLabel->attr('class'));

        $sourceIdField = $formElement->filter('#suite_add_source_id');
        self::assertNull($sourceIdField->attr('class'));

        $sourceIdOptions = $sourceIdField->filter('option');
        foreach ($sourceIdOptions as $sourceIdOption) {
            self::assertInstanceOf(\DOMElement::class, $sourceIdOption);
            self::assertSame(
                $sourceIdOption->getAttribute('value') === $fileSourceId,
                $sourceIdOption->hasAttribute('selected')
            );
        }

        $labelLabel = $formElement->filter('[for=suite_add_label]');
        self::assertSame('error', $labelLabel->attr('class'));

        $labelField = $formElement->filter('#suite_add_label');
        self::assertSame('error', $labelField->attr('class'));
        self::assertSame($suiteLabel, $labelField->attr('value'));

        $testsLabel = $formElement->filter('[for=suite_add_tests]');
        self::assertNull($testsLabel->attr('class'));

        $testsField = $formElement->filter('#suite_add_tests');
        self::assertSame($suiteTests, $testsField->text());
    }
}
