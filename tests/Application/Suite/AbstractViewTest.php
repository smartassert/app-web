<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\SuiteFactory;

abstract class AbstractViewTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider viewSuccessDataProvider
     *
     * @param string[] $tests
     */
    public function testViewSuccess(string $label, array $tests): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceLabel = md5((string) rand());
        $fileSourceId = $fileSourceFactory->create($this->applicationClient, $fileSourceLabel);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);

        $suiteId = $suiteFactory->create($this->applicationClient, $fileSourceId, $label, $tests);

        $suiteViewResponse = $this->applicationClient->makeViewSuiteRequest($suiteId);
        $suiteViewContent = $suiteViewResponse->getBody()->getContents();

        self::assertSame(200, $suiteViewResponse->getStatusCode());
        self::assertStringContainsString('<title>Suite "' . $label . '"</title>', $suiteViewContent);

        $expectedSourceIdPattern = '/<option\s+value="' . $fileSourceId . '"\s+selected/';
        self::assertSame(
            1,
            preg_match($expectedSourceIdPattern, $suiteViewContent)
        );

        $expectedLabelPattern = '/id="suite_update_label"[\s\S]+value="' . $label . '"/';
        self::assertSame(
            1,
            preg_match($expectedLabelPattern, $suiteViewContent)
        );

        $expectedTestsPattern = '/id="suite_update_tests"[\s\S]+>' . implode('\s+', $tests) . '</';
        self::assertSame(
            1,
            preg_match($expectedTestsPattern, $suiteViewContent)
        );
    }

    /**
     * @return array<mixed>
     */
    public function viewSuccessDataProvider(): array
    {
        return [
            'no tests' => [
                'label' => md5((string) rand()),
                'tests' => [],
            ],
            'single test' => [
                'label' => md5((string) rand()),
                'tests' => ['one.yaml'],
            ],
            'multiple tests' => [
                'label' => md5((string) rand()),
                'tests' => ['one.yaml'],
            ],
        ];
    }
}
