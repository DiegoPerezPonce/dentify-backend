<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateTestUserCommand extends Command
{
    // Ya no necesitamos $defaultName estático
    protected static $defaultDescription = 'Crea un usuario de prueba para JWT';

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        // Pasamos el nombre del comando directamente al constructor del padre
        parent::__construct('app:create-test-user');

        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Crear usuario de prueba
        $user = new User();
        $user->setEmail('test@ejemplo.com');
        $user->setUsername('TestUser');
        $user->setRoles(['ROLE_USER']);

        // Hashear la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($user, '123456');
        $user->setPassword($hashedPassword);

        // Guardar en la base de datos
        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('✅ Usuario de prueba creado correctamente!');
        $output->writeln('Email: test@ejemplo.com | Password: 123456');

        return Command::SUCCESS;
    }
}