<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\SuiteFactory;

abstract class AbstractViewTest extends AbstractApplicationTestCase
{
    public function testViewSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceLabel = md5((string) rand());
        $fileSourceId = $fileSourceFactory->create($this->applicationClient, $fileSourceLabel);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);

        $suiteLabel = md5((string) rand());
        $suiteId = $suiteFactory->create($this->applicationClient, $fileSourceId, $suiteLabel, []);

        $suiteViewResponse = $this->applicationClient->makeViewSuiteRequest($suiteId);

        self::assertSame(200, $suiteViewResponse->getStatusCode());
        self::assertStringContainsString(
            '<title>Suite "' . $suiteLabel . '"</title>',
            $suiteViewResponse->getBody()->getContents()
        );
    }
}
