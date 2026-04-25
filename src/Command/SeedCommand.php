<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Patient;
use App\Entity\Dentist;
use App\Entity\Box;
use App\Entity\Appointment;
use App\Entity\StockMaterial;
use App\Entity\ClinicalHistory;
use App\Entity\Odontograma;
use App\Entity\Patologia;
use App\Entity\Radiografia;
use App\Entity\PrimeraVisita;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Seeds the database with 5 records per entity for testing.',
)]
class SeedCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding database...');

        $conn = $this->entityManager->getConnection();
        $io->text('Cleaning up existing data...');
        
        // Disable foreign key checks to truncate/delete comfortably
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $tables = ['appointment', 'patologia', 'odontograma', 'radiografia', 'primera_visita', 'clinical_history', 'patient', 'dentist', 'box', 'stock_material', 'stock_restock', 'protocolo_tratamiento', 'user'];
        foreach ($tables as $table) {
            $conn->executeStatement('TRUNCATE TABLE ' . $table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        try {
            // 1. Users
            $usernames = ['admin', 'dentist1', 'dentist2', 'receptionist', 'staff'];
            $roles = [['ROLE_ADMIN'], ['ROLE_USER'], ['ROLE_USER'], ['ROLE_USER'], ['ROLE_USER']];
            
            foreach ($usernames as $index => $username) {
                $user = new User();
                $user->setEmail($username . "@dentify.com");
                $user->setUsername($username);
                $user->setRoles($roles[$index]);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
                $this->entityManager->persist($user);
            }
            $io->text('Users seeded.');

            // 2. Boxes
            $boxes = [];
            for ($i = 1; $i <= 5; $i++) {
                $box = new Box();
                $box->setNombre("Box $i");
                $box->setEstado($i % 2 == 0 ? 'mantenimiento' : 'disponible');
                $this->entityManager->persist($box);
                $boxes[] = $box;
            }
            $io->text('Boxes seeded.');

            // 3. Dentists
            $dentists = [];
            $specialties = ['General', 'Ortodoncia', 'Implantes', 'Estética', 'Odontopediatría'];
            for ($i = 0; $i < 5; $i++) {
                $dentist = new Dentist();
                $dentist->setNombre("Dentista " . ($i + 1));
                $dentist->setApellidos("Apellido " . ($i + 1));
                $dentist->setEspecialidad($specialties[$i]);
                $dentist->setEmail($usernames[$i] . "@clinica.com");
                $dentist->addBox($boxes[$i]);
                $this->entityManager->persist($dentist);
                $dentists[] = $dentist;
            }
            $io->text('Dentists seeded.');

            // 4. Patients & Related info
            $patients = [];
            for ($i = 1; $i <= 5; $i++) {
                $patient = new Patient();
                $patient->setNombre("Paciente $i");
                $patient->setApellidos("Apellido $i");
                $patient->setDni(str_pad($i, 8, '0', STR_PAD_LEFT) . 'Z');
                $patient->setEmail("paciente$i@gmail.com");
                $patient->setTelefono("61111111" . $i);
                $this->entityManager->persist($patient);
                $patients[] = $patient;

                // Clinical History
                $history = new ClinicalHistory();
                $history->setDescripcion("Consulta inicial para el paciente $i.");
                $history->setTipo("Revision");
                $history->setPatient($patient);
                $this->entityManager->persist($history);

                // Odontograma
                $odontograma = new Odontograma();
                $odontograma->setPatient($patient);
                $this->entityManager->persist($odontograma);

                // Patologia
                $patologia = new Patologia();
                $patologia->setDiente("1" . $i);
                $patologia->setCara("oclusal");
                $patologia->setEstado("caries");
                $patologia->setColor("#FF0000");
                $patologia->setTipoPatologia("patologia");
                $patologia->setOdontograma($odontograma);
                $this->entityManager->persist($patologia);

                // Radiografia
                $radio = new Radiografia();
                $radio->setNombreArchivo("XRAY_$i.jpg");
                $radio->setRutaArchivo("/uploads/radiografias/dummy.jpg");
                $radio->setTipoMime("image/jpeg");
                $radio->setPatient($patient);
                $this->entityManager->persist($radio);

                // Primera Visita
                $visita = new PrimeraVisita();
                $visita->setMotivoConsulta("Revision anual");
                $visita->setNivelDolor(1);
                $visita->setPatient($patient);
                $this->entityManager->persist($visita);
            }
            $io->text('Patients and clinical data seeded.');

            // 5. Appointments
            for ($i = 0; $i < 5; $i++) {
                $appointment = new Appointment();
                $appointment->setPaciente($patients[$i]);
                $appointment->setOdontologo($dentists[$i]);
                $appointment->setBox($boxes[$i]);
                $appointment->setFecha(new \DateTime('+' . ($i + 1) . ' days'));
                $appointment->setHoraInicio(new \DateTime('10:00:00'));
                $appointment->setDuracion(30);
                $appointment->setPrioridad('media');
                $appointment->setEstado('programada');
                $this->entityManager->persist($appointment);
            }
            $io->text('Appointments seeded.');

            // 6. Stock Materials (first line below threshold for low-stock demos / notifications)
            for ($i = 1; $i <= 5; $i++) {
                $material = new StockMaterial();
                $material->setNombre("Material $i");
                $material->setCantidadActual($i === 1 ? 5.0 : 100.0);
                $material->setUnidad('unidades');
                $material->setUmbralMinimo(10.0);
                $this->entityManager->persist($material);
            }
            $io->text('Stock materials persisted.');

            $io->note('Flushing to database...');
            $this->entityManager->flush();
            $io->success('Database seeded successfully!');
        } catch (\Exception $e) {
            $io->error('Error at point: ' . $e->getMessage());
            $io->text($e->getTraceAsString());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
