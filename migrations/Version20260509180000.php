<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260509180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Patient medical_flags (JSON) and medical_notes for critical alerts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient ADD medical_flags JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE patient ADD medical_notes LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient DROP medical_flags');
        $this->addSql('ALTER TABLE patient DROP medical_notes');
    }
}
