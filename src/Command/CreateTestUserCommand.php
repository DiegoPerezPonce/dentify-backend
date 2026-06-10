<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Crea o restaura el usuario admin testuser (login: testuser, password: 123456).',
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = 'testuser';
        $email = 'test@ejemplo.com';
        $password = '123456';

        $user = $this->userRepository->findOneBy(['username' => $username])
            ?? $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $io->text('Creando usuario nuevo…');
        } else {
            $io->text('Usuario existente: restableciendo contraseña y roles…');
            $user->setUsername($username);
            $user->setEmail($email);
        }

        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Usuario testuser listo.');
        $io->listing([
            'Login (usuario): testuser',
            'Login (email): test@ejemplo.com',
            'Contraseña: 123456',
            'Roles: ROLE_ADMIN, ROLE_USER',
        ]);
        $io->note('Si sigues con error en el navegador, cierra sesión o borra auth_token en localStorage y vuelve a entrar.');

        return Command::SUCCESS;
    }
}