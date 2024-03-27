<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Assertions\FormFieldValueAssertionTrait;
use App\Tests\Assertions\SymfonyRedirectResponseAssertionTrait;
use App\Tests\Functional\Application\GetClientAdapterTrait;
use App\Tests\Functional\Application\GetSessionIdentifierTrait;
use App\Tests\Services\ApplicationClient\Client as ApplicationClient;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\SuiteFactory;
use Symfony\Component\DomCrawler\Crawler;

class UpdateTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use GetSessionIdentifierTrait;
    use SymfonyRedirectResponseAssertionTrait;
    use FormFieldValueAssertionTrait;

    /**
     * @dataProvider updateSuccessDataProvider
     *
     * @param callable(ApplicationClient, FileSourceFactory): string         $sourceIdCreator
     * @param string[]                                                       $tests
     * @param callable(ApplicationClient, FileSourceFactory, string): string $updatedSourceIdCreator
     * @param string[]                                                       $updatedTests
     */
    public function testUpdateSuccess(
        callable $sourceIdCreator,
        string $label,
        array $tests,
        callable $updatedSourceIdCreator,
        string $updatedLabel,
        array $updatedTests,
    ): void {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $sourceId = $sourceIdCreator($this->applicationClient, $fileSourceFactory);
        $updatedSourceId = $updatedSourceIdCreator($this->applicationClient, $fileSourceFactory, $sourceId);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);

        $suiteId = $suiteFactory->create($this->applicationClient, $sourceId, $label, $tests);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suite/' . $suiteId,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());
        self::assertSame('Suite "' . $label . '"', $crawler->filter('title')->text());

        $updateSuiteForm = $crawler->filter('#suite_update');
        $this->assertSuiteUpdateForm($updateSuiteForm, $sourceId, $label, $tests, $suiteId);

        $this->kernelBrowser->submit($crawler->filter('#suite_update input[type=submit]')->form([
            'source_id' => $updatedSourceId,
            'label' => $updatedLabel,
            'tests' => implode("\n", $updatedTests),
        ]));

        $response = $this->kernelBrowser->getResponse();
        $this->assertSymfonyRedirectResponse($response, '/suite/' . $suiteId);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suite/' . $suiteId,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());
        self::assertSame('Suite "' . $updatedLabel . '"', $crawler->filter('title')->text());

        $updateSuiteForm = $crawler->filter('#suite_update');
        $this->assertSuiteUpdateForm($updateSuiteForm, $updatedSourceId, $updatedLabel, $updatedTests, $suiteId);
    }

    /**
     * @return array<mixed>
     */
    public function updateSuccessDataProvider(): array
    {
        $label = md5((string) rand());

        return [
            'no changes, empty tests' => [
                'sourceIdCreator' => function (ApplicationClient $client, FileSourceFactory $factory) {
                    return $factory->createRandom($client);
                },
                'label' => $label,
                'tests' => [],
                'updatedSourceIdCreator' => function (
                    ApplicationClient $client,
                    FileSourceFactory $factory,
                    string $sourceId,
                ) {
                    return $sourceId;
                },
                'updatedLabel' => $label,
                'updatedTests' => [],
            ],
            'all change, empty tests to non-empty tests' => [
                'sourceIdCreator' => function (ApplicationClient $client, FileSourceFactory $factory) {
                    return $factory->createRandom($client);
                },
                'label' => md5((string) rand()),
                'tests' => [],
                'updatedSourceIdCreator' => function (ApplicationClient $client, FileSourceFactory $factory) {
                    return $factory->createRandom($client);
                },
                'updatedLabel' => md5((string) rand()),
                'updatedTests' => ['test1.yaml', 'test2.yaml'],
            ],
            'all change, non-empty tests to empty tests' => [
                'sourceIdCreator' => function (ApplicationClient $client, FileSourceFactory $factory) {
                    return $factory->createRandom($client);
                },
                'label' => md5((string) rand()),
                'tests' => ['test1.yaml', 'test2.yaml'],
                'updatedSourceIdCreator' => function (ApplicationClient $client, FileSourceFactory $factory) {
                    return $factory->createRandom($client);
                },
                'updatedLabel' => md5((string) rand()),
                'updatedTests' => [],
            ],
        ];
    }

    /**
     * @dataProvider updateBadRequestDataProvider
     *
     * @param callable(string, ApplicationClient, FileSourceFactory): string $updatedSourceIdCreator
     * @param callable(string): string                                       $updatedLabelCreator
     * @param callable(string[]): string[]                                   $updatedTestsCreator
     * @param callable(string, string, string[]): array<string, string>      $expectedNonErrorFieldsCreator
     */
    public function testUpdateBadRequest(
        ?callable $setup,
        callable $updatedSourceIdCreator,
        callable $updatedLabelCreator,
        callable $updatedTestsCreator,
        string $expectedErrorField,
        string $expectedErrorMessage,
        string $expectedErrorFieldValue,
        callable $expectedNonErrorFieldsCreator,
    ): void {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $suiteLabel = md5((string) rand());
        $suiteTests = ['test1.yaml', 'test2.yaml'];
        $sourceId = $fileSourceFactory->createRandom($this->applicationClient);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);

        $suiteId = $suiteFactory->create($this->applicationClient, $sourceId, $suiteLabel, $suiteTests);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suite/' . $suiteId,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $updatedSourceId = $updatedSourceIdCreator($sourceId, $this->applicationClient, $fileSourceFactory);
        $updatedLabel = $updatedLabelCreator($suiteLabel);
        $updatedTests = $updatedTestsCreator($suiteTests);

        $this->kernelBrowser->submit($crawler->filter('#suite_update input[type=submit]')->form([
            'source_id' => $updatedSourceId,
            'label' => $updatedLabel,
            'tests' => implode("\n", $updatedTests),
        ]));

        $response = $this->kernelBrowser->getResponse();
        $this->assertSymfonyRedirectResponse($response, '/suite/' . $suiteId);

        $crawler = $this->kernelBrowser->request(
            method: 'GET',
            uri: '/suite/' . $suiteId,
            server: [
                'HTTP_COOKIE' => $this->applicationClient->getCredentials(),
            ]
        );

        self::assertSame(200, $this->kernelBrowser->getResponse()->getStatusCode());

        $updateSuiteForm = $crawler->filter('#suite_update');
        self::assertSame('error', $updateSuiteForm->attr('class'));

        $errorContainer = $updateSuiteForm->filter('span.error[data-for=' . $expectedErrorField . ']');
        self::assertSame(1, $errorContainer->count());
        self::assertSame($expectedErrorMessage, $errorContainer->innerText());

        $errorLabel = $updateSuiteForm->filter('label[for=' . $expectedErrorField . ']');
        self::assertSame('error', $errorLabel->attr('class'));

        $errorField = $updateSuiteForm->filter('#' . $expectedErrorField);
        self::assertSame('error', $errorField->attr('class'));
        $this->assertFormFieldValue($errorField, $expectedErrorFieldValue);

        foreach ($expectedNonErrorFieldsCreator($sourceId, $suiteLabel, $suiteTests) as $fieldName => $expectedValue) {
            $label = $updateSuiteForm->filter('label[for=' . $fieldName . ']');
            self::assertNull($label->attr('class'));

            $field = $updateSuiteForm->filter('#' . $fieldName);
            self::assertNull($field->attr('class'));
            $this->assertFormFieldValue($field, $expectedValue);
        }
    }

    /**
     * @return array<mixed>
     */
    public function updateBadRequestDataProvider(): array
    {
        return [
            'label empty' => [
                'setup' => null,
                'updatedSourceIdCreator' => function (string $sourceId) {
                    return $sourceId;
                },
                'updatedLabelCreator' => function () {
                    return '';
                },
                'updatedTestsCreator' => function (array $tests) {
                    return $tests;
                },
                'expectedErrorField' => 'suite_update_label',
                'expectedErrorMessage' => 'This value must not be empty.',
                'expectedErrorFieldValue' => '',
                'expectedNonErrorFieldsCreator' => function (string $sourceId, string $label, array $tests) {
                    return [
                        'suite_update_source_id' => $sourceId,
                        'suite_update_tests' => implode("\n", $tests),
                    ];
                },
            ],
            'test names not yaml filenames' => [
                'setup' => null,
                'updatedSourceIdCreator' => function (string $sourceId) {
                    return $sourceId;
                },
                'updatedLabelCreator' => function (string $label) {
                    return $label;
                },
                'updatedTestsCreator' => function () {
                    return ['test1.txt', 'test2.txt'];
                },
                'expectedErrorField' => 'suite_update_tests',
                'expectedErrorMessage' => 'This value is invalid. It must be a valid "yaml_filename_collection".',
                'expectedErrorFieldValue' => implode("\n", ['test1.txt', 'test2.txt']),
                'expectedNonErrorFieldsCreator' => function (string $sourceId, string $label) {
                    return [
                        'suite_update_source_id' => $sourceId,
                        'suite_update_label' => $label,
                    ];
                },
            ],
        ];
    }

    /**
     * @param string[] $expectedTests
     */
    private function assertSuiteUpdateForm(
        Crawler $updateSuiteForm,
        string $expectedSourceId,
        string $expectedLabel,
        array $expectedTests,
        string $expectedSuiteId,
    ): void {
        self::assertSame(
            $expectedSourceId,
            $updateSuiteForm->filter('#suite_update_source_id [selected]')->attr('value')
        );

        self::assertSame(
            $expectedLabel,
            $updateSuiteForm->filter('#suite_update_label')->attr('value')
        );

        self::assertSame(
            implode("\n", $expectedTests),
            $updateSuiteForm->filter('#suite_update_tests')->html()
        );

        self::assertSame($expectedSuiteId, $updateSuiteForm->filter('[name=suite_id]')->attr('value'));
    }
}
