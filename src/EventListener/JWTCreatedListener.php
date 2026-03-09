<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Add roles to the payload
        $payload['roles'] = $user->getRoles();

        $event->setData($payload);
    }
}
