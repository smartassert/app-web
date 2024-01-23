<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\SignInErrorState;
use App\RedirectRoute\Factory;
use App\Response\RedirectResponseFactory;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class ApiUnauthorizedExceptionHandler implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private RedirectResponseFactory $redirectResponseFactory,
        private Factory $redirectRouteFactory,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleApiUnauthorizedException', 0],
            ],
        ];
    }

    public function handleApiUnauthorizedException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof UnauthorizedException) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if (!$session instanceof Session) {
            return;
        }

        $user = $this->security->getUser();
        if (null === $user) {
            return;
        }

        $session->getFlashBag()->set('error', SignInErrorState::API_UNAUTHORIZED->value);

        $response = $this->redirectResponseFactory->createForSignIn(
            $user->getUserIdentifier(),
            $this->redirectRouteFactory->getDefault(),
        );

        $event->setResponse($response);
    }
}
