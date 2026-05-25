<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clinic schedule settings + dentist weekly shift schedule (Mon–Sat)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clinic_schedule_settings (id INT NOT NULL, open_time VARCHAR(5) NOT NULL, close_time VARCHAR(5) NOT NULL, morning_end_time VARCHAR(5) NOT NULL, afternoon_start_time VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO clinic_schedule_settings (id, open_time, close_time, morning_end_time, afternoon_start_time) VALUES (1, '08:00', '21:00', '15:00', '14:00')");

        $this->addSql('CREATE TABLE dentist_weekly_schedule (id INT AUTO_INCREMENT NOT NULL, dentist_id INT NOT NULL, weekday SMALLINT NOT NULL, shift VARCHAR(16) NOT NULL, default_box_id INT DEFAULT NULL, INDEX IDX_DWS_DENTIST (dentist_id), INDEX IDX_DWS_BOX (default_box_id), UNIQUE INDEX UNIQ_DENTIST_WEEKDAY (dentist_id, weekday), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dentist_weekly_schedule ADD CONSTRAINT FK_DWS_DENTIST FOREIGN KEY (dentist_id) REFERENCES dentist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dentist_weekly_schedule ADD CONSTRAINT FK_DWS_BOX FOREIGN KEY (default_box_id) REFERENCES box (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dentist_weekly_schedule DROP FOREIGN KEY FK_DWS_DENTIST');
        $this->addSql('ALTER TABLE dentist_weekly_schedule DROP FOREIGN KEY FK_DWS_BOX');
        $this->addSql('DROP TABLE dentist_weekly_schedule');
        $this->addSql('DROP TABLE clinic_schedule_settings');
    }
}
