<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

readonly class SessionEraser implements EventSubscriberInterface
{
    /**
     * @return array<mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => [
                ['erase', 0],
            ],
        ];
    }

    public function erase(LogoutEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $session->clear();
    }
}
