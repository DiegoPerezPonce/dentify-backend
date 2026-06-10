<?php

namespace App\Service;

use App\Data\StockAndProtocolSeed;
use App\Entity\ProtocoloTratamiento;
use App\Entity\StockMaterial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Carga inventario y protocolos sin tocar usuarios, login ni citas.
 */
final class StockProtocolSeeder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return array{materials: int, protocolLines: int}
     */
    public function seed(?SymfonyStyle $io = null): array
    {
        $conn = $this->entityManager->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ([
            'appointment_session_material',
            'appointment_session',
            'stock_restock',
            'protocolo_tratamiento',
            'stock_material',
        ] as $table) {
            $conn->executeStatement('TRUNCATE TABLE ' . $table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $this->entityManager->clear();

        $materialByKey = [];
        foreach (StockAndProtocolSeed::materials() as $row) {
            $material = new StockMaterial();
            $material->setNombre($row['nombre']);
                $material->setCantidadActual((float) (int) round($row['cantidad']));
                $material->setUmbralMinimo((float) (int) round($row['umbral']));
            $material->setUnidad($row['unidad']);
            $material->setFechaUltimaReposicion(new \DateTime('-7 days'));
            $this->entityManager->persist($material);
            $materialByKey[$row['key']] = $material;
        }

        $protocolCount = 0;
        foreach (StockAndProtocolSeed::protocols() as $protocol) {
            foreach ($protocol['lines'] as $line) {
                $stock = $materialByKey[$line['material']] ?? null;
                if ($stock === null) {
                    $io?->warning(sprintf(
                        'Protocolo «%s»: material desconocido «%s», línea omitida.',
                        $protocol['treatment'],
                        $line['material']
                    ));
                    continue;
                }

                $protocolo = new ProtocoloTratamiento();
                $protocolo->setNombreTratamiento($protocol['treatment']);
                $protocolo->setMaterial($stock);
                $protocolo->setCantidadNecesaria((float) $line['cantidad']);
                if (isset($line['notas'])) {
                    $protocolo->setNotasPasoAPaso($line['notas']);
                }
                $this->entityManager->persist($protocolo);
                ++$protocolCount;
            }
        }

        $this->entityManager->flush();

        return [
            'materials' => \count($materialByKey),
            'protocolLines' => $protocolCount,
        ];
    }
}
