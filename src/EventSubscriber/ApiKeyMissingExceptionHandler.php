<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ApiKeyMissingException;
use App\RedirectRoute\Factory;
use App\Response\RedirectResponseFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class ApiKeyMissingExceptionHandler implements EventSubscriberInterface
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
                ['handleApiKeyMissingException', 0],
            ],
        ];
    }

    public function handleApiKeyMissingException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof ApiKeyMissingException) {
            return;
        }

        $user = $this->security->getUser();
        $userIdentifier = $user instanceof UserInterface ? $user->getUserIdentifier() : null;

        $this->security->logout(false);

        $event->setResponse($this->redirectResponseFactory->createForSignIn(
            $userIdentifier,
            $this->redirectRouteFactory->getDefault(),
        ));
    }
}
