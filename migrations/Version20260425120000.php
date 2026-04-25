<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Per-material minimum quantity for low-stock alerts (Issue #29).
 */
final class Version20260425120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add umbral_minimo to stock_material for low-stock filtering and alerts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_material ADD umbral_minimo DOUBLE PRECISION DEFAULT 10 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_material DROP umbral_minimo');
    }
}
