<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\DentistRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function __construct(
        private DentistRepository $dentistRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $authUser = $event->getUser();

        if (!$authUser instanceof User) {
            return;
        }

        $userId = $authUser->getId();
        if ($userId === null) {
            return;
        }

        $fresh = $this->userRepository->findForJwtPayload($userId);
        if (!$fresh instanceof User) {
            return;
        }

        $user = $fresh;
        $user->setUltimoAcceso(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $payload['roles'] = $user->getRoles();

        $email = $user->getEmail();
        if (is_string($email) && $email !== '') {
            $payload['email'] = $email;
        }

        $dentist = $user->getDentist();
        if ($dentist === null && is_string($email) && $email !== '') {
            $dentist = $this->dentistRepository->findOneByEmailIgnoreCase($email);
        }
        if ($dentist !== null) {
            $payload['dentistId'] = $dentist->getId();
        }

        $event->setData($payload);
    }
}
