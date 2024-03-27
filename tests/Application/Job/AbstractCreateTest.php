<?php

declare(strict_types=1);

namespace App\Tests\Application\Job;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\SuiteFactory;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    public function testCreateSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);

        $suiteId = $suiteFactory->create($this->applicationClient, $fileSourceId, md5((string) rand()), []);

        $response = $this->applicationClient->makeCreateJobRequest($suiteId);
        self::assertSame(302, $response->getStatusCode());
        self::assertMatchesRegularExpression('#/job/[A-Z0-9]{26}#', $response->getHeaderLine('location'));
    }
}
