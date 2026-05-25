<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ui_preferences JSON to user for per-user theme customization';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD ui_preferences JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP ui_preferences');
    }
}
