<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'User.dentist_id FK (explicit link user → dentist) + backfill from matching email';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD dentist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_USER_DENTIST FOREIGN KEY (dentist_id) REFERENCES dentist (id) ON DELETE SET NULL');
        $this->addSql('UPDATE `user` u INNER JOIN dentist d ON LOWER(TRIM(u.email)) = LOWER(TRIM(d.email)) SET u.dentist_id = d.id WHERE u.dentist_id IS NULL AND u.email IS NOT NULL AND u.email <> \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_USER_DENTIST');
        $this->addSql('ALTER TABLE `user` DROP dentist_id');
    }
}
