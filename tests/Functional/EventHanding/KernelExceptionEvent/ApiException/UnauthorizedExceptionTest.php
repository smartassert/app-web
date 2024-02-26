<?php

declare(strict_types=1);

namespace App\Tests\Functional\EventHanding\KernelExceptionEvent\ApiException;

use App\Enum\ApiService;
use App\Enum\Routes;
use App\Enum\SignInErrorState;
use App\Error\NamedError;
use App\Exception\ApiException;
use App\Tests\Services\SessionHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class UnauthorizedExceptionTest extends WebTestCase
{
    private ExceptionEvent $event;
    private FlashBagAwareSessionInterface $session;

    protected function setUp(): void
    {
        parent::setUp();

        $client = self::createClient();

        $sessionHandler = self::getContainer()->get(SessionHandler::class);
        \assert($sessionHandler instanceof SessionHandler);

        $this->session = $sessionHandler->create();
        $sessionHandler->persist($client, $this->session);

        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        \assert($eventDispatcher instanceof EventDispatcherInterface);
        \assert($eventDispatcher instanceof EventDispatcher);

        $kernel = self::getContainer()->get(KernelInterface::class);
        \assert($kernel instanceof KernelInterface);

        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('getSession')
            ->andReturn($this->session)
        ;

        $request->attributes = new ParameterBag(['_route' => Routes::DASHBOARD_NAME->value]);

        $requestStack = self::getContainer()->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $requestStack->push($request);

        $exception = new ApiException(
            ApiService::USERS,
            new ClientException(md5((string) rand()), new UnauthorizedException())
        );

        $this->event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $eventDispatcher->dispatch($this->event, 'kernel.exception');
    }

    public function testErrorNameIsSetInSession(): void
    {
        $error = $this->session->get('error');
        self::assertInstanceOf(NamedError::class, $error);

        self::assertSame(SignInErrorState::API_UNAUTHORIZED->value, $error->name);
    }

    public function testRedirectResponseIsSetOnEvent(): void
    {
        self::assertSame(
            '/sign-in/?route=eyJuYW1lIjoiZGFzaGJvYXJkIiwicGFyYW1ldGVycyI6W119',
            $this->event->getResponse()?->headers->get('location')
        );
    }
}
