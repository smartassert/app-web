<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\Routes;
use App\Response\RedirectResponseFactory;
use App\Security\RequestTokenExtractor;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class SignInPageRedirector implements EventSubscriberInterface
{
    public function __construct(
        private RequestTokenExtractor $requestTokenExtractor,
        private HttpMessageFactoryInterface $httpMessageFactory,
        private RedirectResponseFactory $redirectResponseFactory,
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
        if (Routes::SIGN_IN_VIEW_NAME->value !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $securityToken = $this->requestTokenExtractor->extract(
            $this->httpMessageFactory->createRequest($event->getRequest())
        );

        if (null === $securityToken) {
            return;
        }

        $event->setResponse($this->redirectResponseFactory->createForRequest($event->getRequest()));
        $event->stopPropagation();
    }
}
