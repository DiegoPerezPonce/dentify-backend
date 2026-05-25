<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Specialty + Treatment catalog; appointment_kind and catalog_treatment_id on appointment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE specialty (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_SPECIALTY_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE treatment (id INT AUTO_INCREMENT NOT NULL, specialty_id INT NOT NULL, name VARCHAR(180) NOT NULL, default_duration_minutes INT NOT NULL, allows_urgency TINYINT(1) NOT NULL, for_first_visit TINYINT(1) NOT NULL, is_custom TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_TREATMENT_SPECIALTY (specialty_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE treatment ADD CONSTRAINT FK_TREATMENT_SPECIALTY FOREIGN KEY (specialty_id) REFERENCES specialty (id) ON DELETE CASCADE');
        $this->addSql("ALTER TABLE appointment ADD appointment_kind VARCHAR(32) NOT NULL DEFAULT 'tratamiento'");
        $this->addSql('ALTER TABLE appointment ADD catalog_treatment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_APPOINTMENT_CATALOG_TREATMENT FOREIGN KEY (catalog_treatment_id) REFERENCES treatment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_APPOINTMENT_CATALOG_TREATMENT ON appointment (catalog_treatment_id)');

        $this->seedCatalog();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_APPOINTMENT_CATALOG_TREATMENT');
        $this->addSql('DROP INDEX IDX_APPOINTMENT_CATALOG_TREATMENT ON appointment');
        $this->addSql('ALTER TABLE appointment DROP catalog_treatment_id');
        $this->addSql('ALTER TABLE appointment DROP appointment_kind');
        $this->addSql('ALTER TABLE treatment DROP FOREIGN KEY FK_TREATMENT_SPECIALTY');
        $this->addSql('DROP TABLE treatment');
        $this->addSql('DROP TABLE specialty');
    }

    private function seedCatalog(): void
    {
        $specialties = [
            'Limpieza dental',
            'Odontología General',
            'Ortodoncia',
            'Endodoncia',
            'Periodoncia',
            'Odontopediatría',
            'Cirugía Oral y Maxilofacial',
            'Prostodoncia',
            'Estética Dental',
            'Implantología',
        ];

        foreach ($specialties as $name) {
            $this->addSql(
                'INSERT INTO specialty (name, active) VALUES (:name, 1)',
                ['name' => $name]
            );
        }

        $treatments = [
            ['Limpieza dental', 'Limpieza dental', 45, 1, 0],
            ['Odontología General', 'Odontología general', 30, 1, 0],
            ['Odontología General', 'Evaluación inicial', 45, 1, 1],
            ['Endodoncia', 'Endodoncia', 60, 1, 0],
            ['Ortodoncia', 'Ortodoncia', 30, 0, 0],
            ['Periodoncia', 'Periodoncia', 45, 1, 0],
            ['Odontopediatría', 'Odontopediatría', 30, 1, 0],
            ['Cirugía Oral y Maxilofacial', 'Cirugía oral', 60, 1, 0],
            ['Prostodoncia', 'Prostodoncia / prótesis', 60, 0, 0],
            ['Estética Dental', 'Estética dental', 45, 0, 0],
            ['Implantología', 'Implantología', 60, 0, 0],
        ];

        foreach ($treatments as [$specialtyName, $treatmentName, $duration, $allowsUrgency, $forFirstVisit]) {
            $this->addSql(
                'INSERT INTO treatment (specialty_id, name, default_duration_minutes, allows_urgency, for_first_visit, is_custom, active)
                 SELECT s.id, :tname, :dur, :urg, :fv, 0, 1 FROM specialty s WHERE s.name = :sname',
                [
                    'tname' => $treatmentName,
                    'dur' => $duration,
                    'urg' => $allowsUrgency,
                    'fv' => $forFirstVisit,
                    'sname' => $specialtyName,
                ]
            );
        }
    }
}
