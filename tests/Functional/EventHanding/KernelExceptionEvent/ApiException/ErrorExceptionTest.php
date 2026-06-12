<?php

declare(strict_types=1);

namespace App\Tests\Functional\EventHanding\KernelExceptionEvent\ApiException;

use App\Enum\ApiService;
use App\Error\NamedError;
use App\Exception\ApiException;
use App\Response\RedirectResponse;
use App\Tests\Services\SessionHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ErrorExceptionTest extends WebTestCase
{
    public function testErrorIsSetInSession(): void
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

        $request->attributes = new ParameterBag(['_route' => 'sources_create_file_source']);

        $requestStack = self::getContainer()->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $requestStack->push($request);

        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(400)
        ;

        $error = \Mockery::mock(ErrorInterface::class);

        $requestMethod = substr(md5((string) rand()), 0, 3);
        $requestRoute = substr(md5((string) rand()), 0, 6);
        $requestSpecification = new RequestSpecification($requestMethod, new RouteRequirements($requestRoute));

        $errorException = new ErrorException($requestSpecification, $error);

        $exception = new ApiException(ApiService::SOURCES, $errorException);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $eventDispatcher->dispatch($event, 'kernel.exception');

        $namedError = $session->get('error');
        self::assertInstanceOf(NamedError::class, $namedError);

        self::assertSame($requestMethod . '_' . $requestRoute, $namedError->name);
        self::assertSame($error, $namedError->error);
    }

    public function testRedirectResponseIsSetOnEvent(): void
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

        $request->attributes = new ParameterBag(['_route' => 'sources_create_file_source']);

        $requestStack = self::getContainer()->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $requestStack->push($request);

        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(400)
        ;

        $error = \Mockery::mock(ErrorInterface::class);

        $redirectResponse = new RedirectResponse(md5((string) rand()));

        $requestMethod = substr(md5((string) rand()), 0, 3);
        $requestRoute = substr(md5((string) rand()), 0, 6);
        $requestSpecification = new RequestSpecification($requestMethod, new RouteRequirements($requestRoute));

        $errorException = new ErrorException($requestSpecification, $error);
        $exception = new ApiException(ApiService::SOURCES, $errorException, $redirectResponse);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $eventDispatcher->dispatch($event, 'kernel.exception');

        self::assertSame($redirectResponse->headers->get('location'), $event->getResponse()?->headers->get('location'));
    }
}
