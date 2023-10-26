<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\RequestTokenExtractor;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SignInPageRedirector implements EventSubscriberInterface
{
    public function __construct(
        private RequestTokenExtractor $requestTokenExtractor,
        private HttpMessageFactoryInterface $httpMessageFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['redirectSignInRequestWithAuthenticationTokenToDashboard', 0],
            ],
        ];
    }

    public function redirectSignInRequestWithAuthenticationTokenToDashboard(RequestEvent $event): void
    {
        if ('sign_in_view' !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $securityToken = $this->requestTokenExtractor->extract(
            $this->httpMessageFactory->createRequest($event->getRequest())
        );

        if (null === $securityToken) {
            return;
        }

        $response = new Response(
            null,
            302,
            [
                'content-type' => null,
                'location' => $this->urlGenerator->generate('dashboard'),
            ]
        );

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
