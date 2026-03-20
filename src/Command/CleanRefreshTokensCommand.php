<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanRefreshTokensCommand extends Command
{
    protected static $defaultName = 'app:clean-refresh-tokens';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Elimina los refresh tokens expirados de la base de datos.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->em->getRepository(RefreshToken::class);

        // Buscar tokens expirados
        $tokens = $repository->createQueryBuilder('t')
            ->where('t.valid <= :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        $count = count($tokens);

        foreach ($tokens as $token) {
            $this->em->remove($token);
        }

        $this->em->flush();

        $output->writeln("$count refresh tokens eliminados.");
        return Command::SUCCESS;
    }
}