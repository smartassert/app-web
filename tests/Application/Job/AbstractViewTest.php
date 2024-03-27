<?php

declare(strict_types=1);

namespace App\Tests\Application\Job;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Services\DataRepository\SourcesRepository;
use App\Tests\Services\EntityFactory\FileSourceFactory;
use App\Tests\Services\EntityFactory\JobFactory;
use App\Tests\Services\EntityFactory\SuiteFactory;

abstract class AbstractViewTest extends AbstractApplicationTestCase
{
    public function testViewSuccess(): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSuites();

        $fileSourceFactory = self::getContainer()->get(FileSourceFactory::class);
        \assert($fileSourceFactory instanceof FileSourceFactory);

        $fileSourceId = $fileSourceFactory->createRandom($this->applicationClient);

        $suiteFactory = self::getContainer()->get(SuiteFactory::class);
        \assert($suiteFactory instanceof SuiteFactory);
        $suiteId = $suiteFactory->create($this->applicationClient, $fileSourceId, md5((string) rand()), []);

        $jobFactory = self::getContainer()->get(JobFactory::class);
        \assert($jobFactory instanceof JobFactory);

        $jobId = $jobFactory->create($this->applicationClient, $suiteId);

        $response = $this->applicationClient->makeViewJobRequest($jobId);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            '<title>Job "' . $jobId . '"</title>',
            $response->getBody()->getContents()
        );
    }
}
