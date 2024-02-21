<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\Routes;
use App\Exception\ApiException;
use App\Response\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class ApiExceptionResponseHandler implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['handleApiException', 0],
            ],
        ];
    }

    public function handleApiException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof ApiException) {
            return;
        }

        $response = $throwable->response;
        if (null === $response) {
            $response = new RedirectResponse($this->urlGenerator->generate(Routes::DASHBOARD_NAME->value));
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }
}
