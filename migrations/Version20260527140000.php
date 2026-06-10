<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Elimina el estado "ocupado" de los boxes; migra registros existentes a "disponible".';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE box SET estado = 'disponible' WHERE LOWER(estado) = 'ocupado'");
        $this->addSql("UPDATE box SET estado = 'mantenimiento' WHERE LOWER(estado) IN ('fuera de servicio', 'fuera_de_servicio')");
    }

    public function down(Schema $schema): void
    {
        // No se puede recuperar qué boxes estaban marcados como ocupados manualmente.
    }
}
