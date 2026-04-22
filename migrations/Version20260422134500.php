<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 * Add treatment, isInfectious and notes fields to appointment table
 */
final class Version20260422134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add treatment, is_infectious and notes fields to appointment table (Issue #23)';
    }

    public function up(Schema $schema): void
    {
        // Add new fields to appointment table
        $this->addSql('ALTER TABLE appointment ADD treatment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD is_infectious TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE appointment ADD notes LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the fields if rolling back
        $this->addSql('ALTER TABLE appointment DROP treatment');
        $this->addSql('ALTER TABLE appointment DROP is_infectious');
        $this->addSql('ALTER TABLE appointment DROP notes');
    }
}
