<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Enum\Routes;
use App\RedirectRoute\RedirectRoute;
use App\RedirectRoute\Serializer;
use App\Tests\Services\ApplicationClient\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Ulid;

abstract class AbstractInvalidTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider handleApiUnauthorizedExceptionDataProvider
     *
     * @param callable(Client, string): ResponseInterface $action
     */
    public function testMakeActionWithInvalidToken(callable $action, RedirectRoute $expectedRedirectRoute): void
    {
        $this->kernelBrowser->getCookieJar()->clear();

        $response = $action($this->applicationClient, '');
        self::assertSame(302, $response->getStatusCode());

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        \assert($urlGenerator instanceof UrlGeneratorInterface);

        $redirectRouteSerializer = self::getContainer()->get(Serializer::class);
        \assert($redirectRouteSerializer instanceof Serializer);

        $expected = $urlGenerator->generate(
            Routes::SIGN_IN_VIEW_NAME->value,
            ['route' => $redirectRouteSerializer->serialize($expectedRedirectRoute)]
        );

        self::assertSame($expected, $response->getHeaderLine('location'));
    }

    /**
     * @return array<mixed>
     */
    public function handleApiUnauthorizedExceptionDataProvider(): array
    {
        $sourceId = (string) new Ulid();

        return [
            'view dashboard' => [
                'action' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeDashboardReadRequest($credentials);
                },
                'expectedRedirectRoute' => new RedirectRoute(Routes::DASHBOARD_NAME->value, []),
            ],
            'view sources' => [
                'action' => function (Client $applicationClient, string $credentials) use ($sourceId) {
                    return $applicationClient->makeFileSourceReadRequest($sourceId, $credentials);
                },
                'expectedRedirectRoute' => new RedirectRoute('sources_view_file_source', ['id' => $sourceId]),
            ],
            'view file source' => [
                'action' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeSourcesReadRequest($credentials);
                },
                'expectedRedirectRoute' => new RedirectRoute('sources'),
            ],
            'add file source' => [
                'action' => function (Client $applicationClient, string $credentials) {
                    return $applicationClient->makeFileSourceAddRequest(md5((string) rand()), $credentials);
                },
                'expectedRedirectRoute' => new RedirectRoute('sources_create_file_source'),
            ],
        ];
    }
}
