<?php

namespace App\Controller;

use App\Dto\UserUiPreferencesUpdateDTO;
use App\Entity\User;
use App\Mapper\UiPreferencesMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class MeController extends AbstractController
{
    public function __construct(
        private UiPreferencesMapper $uiPreferencesMapper,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/preferences', name: 'app_me_preferences_get', methods: ['GET'])]
    public function getPreferences(): JsonResponse
    {
        $user = $this->requireUser();

        return $this->json($this->uiPreferencesMapper->fromUser($user));
    }

    #[Route('/preferences', name: 'app_me_preferences_patch', methods: ['PATCH'])]
    public function patchPreferences(#[MapRequestPayload] UserUiPreferencesUpdateDTO $dto): JsonResponse
    {
        $user = $this->requireUser();
        $this->uiPreferencesMapper->applyUpdate($user, $dto);
        $this->entityManager->flush();

        return $this->json($this->uiPreferencesMapper->fromUser($user));
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
