<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Data\TreatmentCatalogSeed;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Treatment categories (clinical groups) + full treatment catalog; dentist specialty on treatment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE treatment_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, sort_order INT NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_TREATMENT_CATEGORY_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE treatment ADD category_id INT DEFAULT NULL, ADD dentist_specialty_id INT DEFAULT NULL');
        $this->addSql('DELETE FROM treatment');
        $this->addSql('ALTER TABLE treatment DROP FOREIGN KEY FK_TREATMENT_SPECIALTY');
        $this->addSql('DROP INDEX IDX_TREATMENT_SPECIALTY ON treatment');
        $this->addSql('ALTER TABLE treatment DROP specialty_id');

        foreach (TreatmentCatalogSeed::categories() as $block) {
            $this->addSql(
                'INSERT INTO treatment_category (name, sort_order, active) VALUES (:name, :sort, 1)',
                ['name' => $block['category'], 'sort' => $block['sort']]
            );

            foreach ($block['treatments'] as $t) {
                $urgent = array_key_exists('urgent', $t) ? (int) (bool) $t['urgent'] : 1;
                $firstVisit = (int) (bool) ($t['first_visit'] ?? false);
                $this->addSql(
                    'INSERT INTO treatment (category_id, dentist_specialty_id, name, default_duration_minutes, allows_urgency, for_first_visit, is_custom, active)
                     SELECT c.id, s.id, :tname, :dur, :urg, :fv, 0, 1
                     FROM treatment_category c, specialty s
                     WHERE c.name = :cname AND s.name = :dspec',
                    [
                        'tname' => $t['name'],
                        'dur' => $t['duration'],
                        'urg' => $urgent,
                        'fv' => $firstVisit,
                        'cname' => $block['category'],
                        'dspec' => $t['dentist_specialty'],
                    ]
                );
            }
        }

        $this->addSql('ALTER TABLE treatment MODIFY category_id INT NOT NULL, MODIFY dentist_specialty_id INT NOT NULL');
        $this->addSql('ALTER TABLE treatment ADD CONSTRAINT FK_TREATMENT_CATEGORY FOREIGN KEY (category_id) REFERENCES treatment_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE treatment ADD CONSTRAINT FK_TREATMENT_DENTIST_SPECIALTY FOREIGN KEY (dentist_specialty_id) REFERENCES specialty (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_TREATMENT_CATEGORY ON treatment (category_id)');
        $this->addSql('CREATE INDEX IDX_TREATMENT_DENTIST_SPECIALTY ON treatment (dentist_specialty_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE treatment DROP FOREIGN KEY FK_TREATMENT_CATEGORY');
        $this->addSql('ALTER TABLE treatment DROP FOREIGN KEY FK_TREATMENT_DENTIST_SPECIALTY');
        $this->addSql('DROP INDEX IDX_TREATMENT_CATEGORY ON treatment');
        $this->addSql('DROP INDEX IDX_TREATMENT_DENTIST_SPECIALTY ON treatment');
        $this->addSql('ALTER TABLE treatment DROP category_id, DROP dentist_specialty_id');
        $this->addSql('ALTER TABLE treatment ADD specialty_id INT NOT NULL');
        $this->addSql('ALTER TABLE treatment ADD CONSTRAINT FK_TREATMENT_SPECIALTY FOREIGN KEY (specialty_id) REFERENCES specialty (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_TREATMENT_SPECIALTY ON treatment (specialty_id)');
        $this->addSql('DROP TABLE treatment_category');
    }
}
