<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pedagogical notices for admin → students (Backend #36, FE #21)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pedagogical_notice (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            body LONGTEXT NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME NOT NULL,
            expires_at DATETIME DEFAULT NULL,
            requires_ack TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            created_by_id INT DEFAULT NULL,
            INDEX IDX_PEDAGOGICAL_NOTICE_PUBLISHED (published_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pedagogical_notice ADD CONSTRAINT FK_PEDAGOGICAL_NOTICE_USER FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pedagogical_notice DROP FOREIGN KEY FK_PEDAGOGICAL_NOTICE_USER');
        $this->addSql('DROP TABLE pedagogical_notice');
    }
}
