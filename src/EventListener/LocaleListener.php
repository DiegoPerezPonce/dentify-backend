<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'es')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // 1. Try to get locale from query parameter
        if ($locale = $request->query->get('_locale')) {
            $request->setLocale($locale);
            return;
        }

        // 2. Try to get locale from Accept-Language header
        $request->setLocale($request->getPreferredLanguage(['es', 'en', 'ca']) ?: $this->defaultLocale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must be registered before the default LocaleListener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
