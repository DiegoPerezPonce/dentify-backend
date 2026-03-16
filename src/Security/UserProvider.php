<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Symfony llama a este método cuando alguien intenta autenticarse
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Buscar usuario por email (o username si lo prefieres)
        $user = $this->userRepository->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('Usuario "%s" no encontrado.', $identifier));
        }

        return $user;
    }

    /**
     * Compatibilidad con versiones antiguas de Symfony
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Recarga el usuario desde la base de datos
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'Clase de usuario no soportada: "%s".',
                get_class($user)
            ));
        }

        $refreshedUser = $this->userRepository->find($user->getId());

        if (!$refreshedUser) {
            throw new UserNotFoundException('El usuario ya no existe.');
        }

        return $refreshedUser;
    }

    /**
     * Indica qué clase de usuario soporta este provider
     */
    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}