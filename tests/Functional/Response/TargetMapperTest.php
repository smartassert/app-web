<?php

declare(strict_types=1);

namespace App\Tests\Functional\Response;

use App\Response\TargetMapper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class TargetMapperTest extends WebTestCase
{
    /**
     * @dataProvider targetMapperConfigurationDataProvider
     */
    public function testTargetMapperConfiguration(Request $request, ?string $expected): void
    {
        $targetMapper = self::getContainer()->get(TargetMapper::class);
        \assert($targetMapper instanceof TargetMapper);

        self::assertSame($expected, $targetMapper->getForRequest($request));
    }

    /**
     * @return array<mixed>
     */
    public function targetMapperConfigurationDataProvider(): array
    {
        return [
            'request has no _route attribute' => [
                'request' => (function () {
                    $request = \Mockery::mock(Request::class);
                    $request->attributes = new ParameterBag();

                    return $request;
                })(),
                'expected' => null,
            ],
            'sign in => dashboard' => [
                'request' => (function () {
                    $request = \Mockery::mock(Request::class);
                    $request->attributes = new ParameterBag(['_route' => 'sign_in_view']);

                    return $request;
                })(),
                'expected' => 'dashboard',
            'add file source => sources' => [
                'request' => (function () {
                    $request = \Mockery::mock(Request::class);
                    $request->attributes = new ParameterBag(['_route' => 'sources_add_file_source']);

                    return $request;
                })(),
                'expected' => 'sources',
            ],
        ];
    }
}
