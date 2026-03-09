<?php

namespace App\Mapper;

use App\Dto\UserCreateDTO;
use App\Dto\UserResponseDTO;
use App\Dto\UserUpdateDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function toEntity(UserCreateDTO $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setNombreUsuario($dto->nombre_usuario);
        $user->setRoles($dto->roles);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        return $user;
    }

    public function updateEntity(User $user, UserUpdateDTO $dto): User
    {
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        if ($dto->nombre_usuario !== null) {
            $user->setNombreUsuario($dto->nombre_usuario);
        }
        if ($dto->roles !== null) {
            $user->setRoles($dto->roles);
        }
        if ($dto->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
            $user->setPassword($hashedPassword);
        }

        return $user;
    }

    public function toResponseDto(User $user): UserResponseDTO
    {
        $dto = new UserResponseDTO();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->nombre_usuario = $user->getNombreUsuario();
        $dto->roles = $user->getRoles();
        $dto->ultimo_acceso = $user->getUltimoAcceso()?->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
