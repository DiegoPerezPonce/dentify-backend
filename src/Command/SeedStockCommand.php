<?php

namespace App\Command;

use App\Service\StockProtocolSeeder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-stock',
    description: 'Recarga stock y protocolos sin borrar usuarios, pacientes ni citas (no afecta al login).',
)]
class SeedStockCommand extends Command
{
    public function __construct(
        private StockProtocolSeeder $stockProtocolSeeder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Recargando inventario y protocolos…');
        $io->note('No se modifican usuarios ni sesiones JWT. Las sesiones clínicas en curso se reinician.');

        try {
            $result = $this->stockProtocolSeeder->seed($io);
            $io->success(sprintf(
                '%d materiales y %d líneas de protocolo cargados. El login sigue igual.',
                $result['materials'],
                $result['protocolLines']
            ));
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
