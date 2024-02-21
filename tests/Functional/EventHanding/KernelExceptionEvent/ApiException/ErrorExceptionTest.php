<?php

declare(strict_types=1);

namespace App\Tests\Functional\EventHanding\KernelExceptionEvent\ApiException;

use App\Enum\ApiService;
use App\Exception\ApiException;
use App\Response\RedirectResponse;
use App\Tests\Services\SessionHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ErrorExceptionTest extends WebTestCase
{
    public function testErrorNameAndErrorAreSetInSession(): void
    {
        $client = self::createClient();

        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $session = $sessionHandler->create();
        $sessionHandler->persist($client, $session);

        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        \assert($eventDispatcher instanceof EventDispatcherInterface);
        \assert($eventDispatcher instanceof EventDispatcher);

        $kernel = self::getContainer()->get(KernelInterface::class);
        \assert($kernel instanceof KernelInterface);

        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('getSession')
            ->andReturn($session)
        ;

        $request->attributes = new ParameterBag(['_route' => 'sources_add_file_source']);

        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(400)
        ;

        $exceptionRequestName = md5((string) rand());
        $error = \Mockery::mock(ErrorInterface::class);

        $clientException = new ClientException($exceptionRequestName, new ErrorException($error));
        $exception = new ApiException(ApiService::SOURCES, $clientException);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $eventDispatcher->dispatch($event, 'kernel.exception');

        self::assertSame($exceptionRequestName, $session->getFlashBag()->get('error_name')[0]);
        self::assertSame($error, $session->get('error'));
    }

    public function testRedirectResponseIsSetOnEvent(): void
    {
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        \assert($eventDispatcher instanceof EventDispatcherInterface);
        \assert($eventDispatcher instanceof EventDispatcher);

        $kernel = self::getContainer()->get(KernelInterface::class);
        \assert($kernel instanceof KernelInterface);

        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('getSession')
            ->andReturn(\Mockery::mock(SessionInterface::class))
        ;

        $request->attributes = new ParameterBag(['_route' => 'sources_add_file_source']);

        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(400)
        ;

        $exceptionRequestName = md5((string) rand());
        $error = \Mockery::mock(ErrorInterface::class);

        $redirectResponse = new RedirectResponse(md5((string) rand()));

        $clientException = new ClientException($exceptionRequestName, new ErrorException($error));
        $exception = new ApiException(ApiService::SOURCES, $clientException, $redirectResponse);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $eventDispatcher->dispatch($event, 'kernel.exception');

        self::assertSame($redirectResponse->headers->get('location'), $event->getResponse()?->headers->get('location'));
    }
}
