<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Appointment clinical session and consumed stock materials';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE appointment_session (
            id INT AUTO_INCREMENT NOT NULL,
            appointment_id INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            started_at DATETIME NOT NULL,
            completed_at DATETIME DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            UNIQUE INDEX UNIQ_APPT_SESSION_APPT (appointment_id),
            INDEX IDX_APPT_SESSION_STATUS (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE appointment_session ADD CONSTRAINT FK_APPT_SESSION_APPT FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE appointment_session_material (
            id INT AUTO_INCREMENT NOT NULL,
            session_id INT NOT NULL,
            stock_material_id INT NOT NULL,
            cantidad_planificada DOUBLE PRECISION NOT NULL,
            cantidad_usada DOUBLE PRECISION NOT NULL,
            stock_reduced TINYINT(1) NOT NULL,
            from_protocol TINYINT(1) NOT NULL,
            protocol_notes LONGTEXT DEFAULT NULL,
            INDEX IDX_SESSION_MAT_SESSION (session_id),
            INDEX IDX_SESSION_MAT_STOCK (stock_material_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE appointment_session_material ADD CONSTRAINT FK_SESSION_MAT_SESSION FOREIGN KEY (session_id) REFERENCES appointment_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_session_material ADD CONSTRAINT FK_SESSION_MAT_STOCK FOREIGN KEY (stock_material_id) REFERENCES stock_material (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment_session_material DROP FOREIGN KEY FK_SESSION_MAT_SESSION');
        $this->addSql('ALTER TABLE appointment_session_material DROP FOREIGN KEY FK_SESSION_MAT_STOCK');
        $this->addSql('DROP TABLE appointment_session_material');
        $this->addSql('ALTER TABLE appointment_session DROP FOREIGN KEY FK_APPT_SESSION_APPT');
        $this->addSql('DROP TABLE appointment_session');
    }
}
