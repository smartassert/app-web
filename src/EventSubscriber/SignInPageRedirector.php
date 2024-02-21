<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\Routes;
use App\Response\RedirectResponse;
use App\Security\RequestTokenExtractor;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
                ['redirectSignInRequestWithAuthentication', 0],
            ],
        ];
    }

    public function redirectSignInRequestWithAuthentication(RequestEvent $event): void
    {
        if (Routes::SIGN_IN_VIEW_NAME->value !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $securityToken = $this->requestTokenExtractor->extract(
            $this->httpMessageFactory->createRequest($event->getRequest())
        );

        if (null === $securityToken) {
            return;
        }

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate(Routes::DASHBOARD_NAME->value))
        );
        $event->stopPropagation();
    }
}
