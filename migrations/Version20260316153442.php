<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316153442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, hora_inicio TIME NOT NULL, duracion INT NOT NULL, prioridad VARCHAR(20) NOT NULL, estado VARCHAR(20) NOT NULL, paciente_id INT NOT NULL, odontologo_id INT NOT NULL, box_id INT NOT NULL, INDEX IDX_FE38F8447310DAD4 (paciente_id), INDEX IDX_FE38F844C1C6A359 (odontologo_id), INDEX IDX_FE38F844D8177B3F (box_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE box (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion LONGTEXT DEFAULT NULL, estado VARCHAR(50) NOT NULL, dentist_id INT DEFAULT NULL, INDEX IDX_8A9483A1CE0A142 (dentist_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE clinical_history (id INT AUTO_INCREMENT NOT NULL, descripcion LONGTEXT NOT NULL, fecha DATETIME NOT NULL, tipo VARCHAR(255) NOT NULL, observaciones LONGTEXT DEFAULT NULL, patient_id INT NOT NULL, INDEX IDX_E308CDA16B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dentist (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, especialidad VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE odontograma (id INT AUTO_INCREMENT NOT NULL, dientes JSON NOT NULL, fecha_creacion DATETIME NOT NULL, fecha_modificacion DATETIME NOT NULL, patient_id INT NOT NULL, INDEX IDX_9E4EE42A6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, dni VARCHAR(20) NOT NULL, nss VARCHAR(20) DEFAULT NULL, telefono VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, enfermedades LONGTEXT DEFAULT NULL, alergias LONGTEXT DEFAULT NULL, historial_clinico LONGTEXT DEFAULT NULL, datos_facturacion LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_PATIENT_DNI (dni), UNIQUE INDEX UNIQ_PATIENT_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patologia (id INT AUTO_INCREMENT NOT NULL, diente VARCHAR(255) NOT NULL, cara VARCHAR(255) NOT NULL, tipo_patologia VARCHAR(255) NOT NULL, estado VARCHAR(255) NOT NULL, color VARCHAR(50) NOT NULL, fecha DATETIME NOT NULL, odontograma_id INT NOT NULL, INDEX IDX_17FB1F75F8ED5CE7 (odontograma_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE primera_visita (id INT AUTO_INCREMENT NOT NULL, motivo_consulta LONGTEXT NOT NULL, nivel_dolor INT NOT NULL, observaciones LONGTEXT DEFAULT NULL, fecha DATETIME NOT NULL, odontograma_id INT DEFAULT NULL, patient_id INT NOT NULL, INDEX IDX_E9B37AF66B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE protocolo_tratamiento (id INT AUTO_INCREMENT NOT NULL, nombre_tratamiento VARCHAR(255) NOT NULL, cantidad_necesaria DOUBLE PRECISION NOT NULL, notas_paso_a_paso LONGTEXT DEFAULT NULL, material_id INT NOT NULL, INDEX IDX_FE29C314E308AC6F (material_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE radiografia (id INT AUTO_INCREMENT NOT NULL, nombre_archivo VARCHAR(255) NOT NULL, ruta_archivo VARCHAR(255) NOT NULL, tipo_mime VARCHAR(100) NOT NULL, fecha_subida DATETIME NOT NULL, patient_id INT NOT NULL, INDEX IDX_9790483D6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_material (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, cantidad_actual DOUBLE PRECISION NOT NULL, unidad VARCHAR(50) NOT NULL, fecha_ultima_reposicion DATE DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_restock (id INT AUTO_INCREMENT NOT NULL, cantidad_recibida DOUBLE PRECISION NOT NULL, proveedor VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, material_id INT NOT NULL, INDEX IDX_2A10314BE308AC6F (material_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, ultimo_acceso DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8447310DAD4 FOREIGN KEY (paciente_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844C1C6A359 FOREIGN KEY (odontologo_id) REFERENCES dentist (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844D8177B3F FOREIGN KEY (box_id) REFERENCES box (id)');
        $this->addSql('ALTER TABLE box ADD CONSTRAINT FK_8A9483A1CE0A142 FOREIGN KEY (dentist_id) REFERENCES dentist (id)');
        $this->addSql('ALTER TABLE clinical_history ADD CONSTRAINT FK_E308CDA16B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE odontograma ADD CONSTRAINT FK_9E4EE42A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE patologia ADD CONSTRAINT FK_17FB1F75F8ED5CE7 FOREIGN KEY (odontograma_id) REFERENCES odontograma (id)');
        $this->addSql('ALTER TABLE primera_visita ADD CONSTRAINT FK_E9B37AF66B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE protocolo_tratamiento ADD CONSTRAINT FK_FE29C314E308AC6F FOREIGN KEY (material_id) REFERENCES stock_material (id)');
        $this->addSql('ALTER TABLE radiografia ADD CONSTRAINT FK_9790483D6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE stock_restock ADD CONSTRAINT FK_2A10314BE308AC6F FOREIGN KEY (material_id) REFERENCES stock_material (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8447310DAD4');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844C1C6A359');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844D8177B3F');
        $this->addSql('ALTER TABLE box DROP FOREIGN KEY FK_8A9483A1CE0A142');
        $this->addSql('ALTER TABLE clinical_history DROP FOREIGN KEY FK_E308CDA16B899279');
        $this->addSql('ALTER TABLE odontograma DROP FOREIGN KEY FK_9E4EE42A6B899279');
        $this->addSql('ALTER TABLE patologia DROP FOREIGN KEY FK_17FB1F75F8ED5CE7');
        $this->addSql('ALTER TABLE primera_visita DROP FOREIGN KEY FK_E9B37AF66B899279');
        $this->addSql('ALTER TABLE protocolo_tratamiento DROP FOREIGN KEY FK_FE29C314E308AC6F');
        $this->addSql('ALTER TABLE radiografia DROP FOREIGN KEY FK_9790483D6B899279');
        $this->addSql('ALTER TABLE stock_restock DROP FOREIGN KEY FK_2A10314BE308AC6F');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE box');
        $this->addSql('DROP TABLE clinical_history');
        $this->addSql('DROP TABLE dentist');
        $this->addSql('DROP TABLE odontograma');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE patologia');
        $this->addSql('DROP TABLE primera_visita');
        $this->addSql('DROP TABLE protocolo_tratamiento');
        $this->addSql('DROP TABLE radiografia');
        $this->addSql('DROP TABLE stock_material');
        $this->addSql('DROP TABLE stock_restock');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
