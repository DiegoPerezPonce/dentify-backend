<?php

namespace App\Mapper;

use App\Dto\UserCreateDTO;
use App\Dto\UserResponseDTO;
use App\Dto\UserUpdateDTO;
use App\Entity\Dentist;
use App\Entity\User;
use App\Repository\DentistRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private DentistRepository $dentistRepository,
        private UiPreferencesMapper $uiPreferencesMapper,
    ) {}

    public function toEntity(UserCreateDTO $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setUsername($dto->nombre_usuario);
        $user->setRoles($dto->roles);
        $user->setDentist($this->buildDentistFromCreate($dto));

        return $user;
    }

    /**
     * @param bool $dentistIdInRequest true si el JSON incluye la clave `dentistId` (PUT/PATCH desde admin)
     */
    public function updateEntity(User $user, UserUpdateDTO $dto, bool $dentistIdInRequest = false): User
    {
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        if ($dto->nombre_usuario !== null) {
            $user->setUsername($dto->nombre_usuario);
        }
        if ($dto->roles !== null) {
            $user->setRoles($dto->roles);
        }
        if ($dto->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
            $user->setPassword($hashedPassword);
        }
        if ($dentistIdInRequest) {
            $user->setDentist($this->resolveDentistOrNull($this->coerceDentistFk($dto->dentistId ?? $dto->dentist_id)));
        }

        return $user;
    }

    public function toResponseDto(User $user): UserResponseDTO
    {
        $dto = new UserResponseDTO();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->nombre_usuario = $user->getUsername();
        $dto->roles = $user->getRoles();
        $dto->ultimo_acceso = $user->getUltimoAcceso()?->format(\DateTimeInterface::ATOM);
        $dentist = $user->getDentist();
        $dto->dentistId = $dentist?->getId();
        if ($dentist !== null) {
            $dto->dentistNombre = $dentist->getNombre();
            $dto->dentistApellidos = $dentist->getApellidos();
            $dto->dentistEspecialidad = $dentist->getEspecialidad();
        }
        $dto->ui_preferences = $this->uiPreferencesMapper->fromUser($user);

        return $dto;
    }

    private function resolveDentistOrNull(?int $dentistId): ?Dentist
    {
        if ($dentistId === null) {
            return null;
        }
        $dentist = $this->dentistRepository->find($dentistId);
        if ($dentist === null) {
            throw new BadRequestHttpException('dentistId no válido');
        }

        return $dentist;
    }

    private function coerceDentistFk(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (\is_int($v) && $v > 0) {
            return $v;
        }
        if (\is_string($v) && preg_match('/^\d+$/', $v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }
        if (\is_float($v) && $v > 0 && floor($v) === $v) {
            return (int) $v;
        }
        throw new BadRequestHttpException('dentistId no válido');
    }

    private function buildDentistFromCreate(UserCreateDTO $dto): Dentist
    {
        $nombre = trim((string) ($dto->dentistNombre ?? ''));
        $apellidos = trim((string) ($dto->dentistApellidos ?? ''));
        $especialidad = trim((string) ($dto->dentistEspecialidad ?? ''));
        $email = trim((string) ($dto->email ?? ''));

        if ($nombre === '' || $apellidos === '' || $especialidad === '' || $email === '') {
            throw new BadRequestHttpException('Faltan datos del odontólogo para crear el usuario.');
        }

        $dentist = new Dentist();
        $dentist->setNombre($nombre);
        $dentist->setApellidos($apellidos);
        $dentist->setEspecialidad($especialidad);
        $dentist->setEmail($email);

        return $dentist;
    }
}
