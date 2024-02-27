<?php

declare(strict_types=1);

namespace App\Tests\Services\EntityFactory;

use App\Tests\Services\ApplicationClient\Client;
use PHPUnit\Framework\Assert;

readonly class FileSourceFileFactory
{
    public function create(Client $client, string $fileSourceId, string $filename, string $content): void
    {
        $response = $client->makeFileSourceFileCreateRequest($fileSourceId, $filename, $content);
        $expectedFileSourceUrl = '/sources/file/' . $fileSourceId;

        Assert::assertSame(302, $response->getStatusCode());
        Assert::assertSame($expectedFileSourceUrl, $response->getHeaderLine('location'));
    }
}
