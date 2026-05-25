<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add terminos_generales_aceptados to patient (RGPD / clinic terms consent)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient ADD terminos_generales_aceptados TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient DROP terminos_generales_aceptados');
    }
}
