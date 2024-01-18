<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\ApplicationClient\Client;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractApplicationTestCase extends WebTestCase
{
    protected KernelBrowser $kernelBrowser;
    protected Client $applicationClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->kernelBrowser = self::createClient();
        $this->applicationClient = new Client($this->getClientAdapter());
    }

    public function getClientAdapter(): ClientInterface
    {
        return \Mockery::mock(ClientInterface::class);
    }

    abstract protected function getSessionIdentifier(): string;
}
