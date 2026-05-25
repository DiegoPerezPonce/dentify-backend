<?php

namespace App\Controller;

use App\Dto\UserCreateDTO;
use App\Dto\UserUpdateDTO;
use App\Entity\User;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserMapper $userMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_user_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $users = $request->query->getBoolean('clinicalProfiles')
            ? $this->userRepository->findAllWithDentistProfile()
            : $this->userRepository->findAll();
        $responseDtos = array_map(
            fn(User $user) => $this->userMapper->toResponseDto($user),
            $users
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_user_get', methods: ['GET'])]
    public function get(User $user): JsonResponse
    {
        return $this->json($this->userMapper->toResponseDto($user));
    }

    #[Route('', name: 'app_user_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] UserCreateDTO $userCreateDTO,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {

        // DTO → entidad (sin contraseña; el mapper no hashea para evitar doble hash)
        $user = $this->userMapper->toEntity($userCreateDTO);
        $user->setPassword($passwordHasher->hashPassword($user, $userCreateDTO->password));

        // Guardar ficha de odontólogo y usuario en una sola operación.
        $dentist = $user->getDentist();
        if ($dentist !== null && $dentist->getId() === null) {
            $this->entityManager->persist($dentist);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Respuesta
        return $this->json(
            $this->userMapper->toResponseDto($user),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_user_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, User $user, #[MapRequestPayload] UserUpdateDTO $userUpdateDTO): JsonResponse
    {
        $raw = json_decode($request->getContent(), true);
        $dentistIdInRequest = \is_array($raw)
            && (\array_key_exists('dentistId', $raw) || \array_key_exists('dentist_id', $raw));

        $user = $this->userMapper->updateEntity($user, $userUpdateDTO, $dentistIdInRequest);

        $this->entityManager->flush();

        return $this->json($this->userMapper->toResponseDto($user));
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

