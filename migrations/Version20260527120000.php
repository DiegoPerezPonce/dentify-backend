<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize stock_material.umbral_minimo: default 10 for materials below 10 (keep demo low-stock item)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE stock_material SET umbral_minimo = 10
             WHERE umbral_minimo < 10
             AND nombre NOT LIKE '%reposición urgente%'"
        );
    }

    public function down(Schema $schema): void
    {
        // No revert: previous per-item thresholds are not recoverable.
    }
}
