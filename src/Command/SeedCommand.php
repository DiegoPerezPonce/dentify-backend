<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Patient;
use App\Entity\Dentist;
use App\Entity\Box;
use App\Entity\Appointment;
use App\Entity\Treatment;
use App\Entity\PedagogicalNotice;
use App\Data\PatientDemoSeed;
use App\Service\StockProtocolSeeder;
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
        private UserPasswordHasherInterface $passwordHasher,
        private StockProtocolSeeder $stockProtocolSeeder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding database...');
        $io->warning([
            'Este comando BORRA usuarios, pacientes y citas.',
            'Para recargar solo stock/protocolos sin afectar el login: php bin/console app:seed-stock',
        ]);

        $conn = $this->entityManager->getConnection();
        $io->text('Cleaning up existing data...');
        
        // Disable foreign key checks to truncate/delete comfortably
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $tables = [
            'appointment_session_material',
            'appointment_session',
            'appointment',
            'patologia',
            'odontograma',
            'radiografia',
            'primera_visita',
            'clinical_history',
            'patient',
            'dentist',
            'box',
            'pedagogical_notice',
            'user',
        ];
        foreach ($tables as $table) {
            $conn->executeStatement('TRUNCATE TABLE ' . $table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        try {
            // 1. Users (testuser = cuenta principal de desarrollo)
            $userDefs = [
                ['username' => 'testuser', 'email' => 'test@ejemplo.com', 'password' => '123456', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']],
                ['username' => 'admin', 'email' => 'admin@dentify.com', 'password' => 'password123', 'roles' => ['ROLE_ADMIN']],
                ['username' => 'dentist1', 'email' => 'dentist1@dentify.com', 'password' => 'password123', 'roles' => ['ROLE_USER']],
                ['username' => 'dentist2', 'email' => 'dentist2@dentify.com', 'password' => 'password123', 'roles' => ['ROLE_USER']],
                ['username' => 'receptionist', 'email' => 'receptionist@dentify.com', 'password' => 'password123', 'roles' => ['ROLE_USER']],
                ['username' => 'staff', 'email' => 'staff@dentify.com', 'password' => 'password123', 'roles' => ['ROLE_USER']],
            ];

            $adminUser = null;
            foreach ($userDefs as $def) {
                $user = new User();
                $user->setEmail($def['email']);
                $user->setUsername($def['username']);
                $user->setRoles($def['roles']);
                $user->setPassword($this->passwordHasher->hashPassword($user, $def['password']));
                $this->entityManager->persist($user);
                if ($def['username'] === 'testuser') {
                    $adminUser = $user;
                }
            }
            $io->text('Users seeded (testuser / 123456 incluido).');

            // 2. Boxes
            $boxes = [];
            for ($i = 1; $i <= 5; $i++) {
                $box = new Box();
                $box->setNombre("Box $i");
                // Solo Box 5 en mantenimiento (sin citas de demo); el resto disponibles
                $box->setEstado($i === 5 ? 'mantenimiento' : 'disponible');
                $this->entityManager->persist($box);
                $boxes[] = $box;
            }
            $io->text('Boxes seeded.');

            // 3. Dentists
            $dentists = [];
            $specialties = ['General', 'Ortodoncia', 'Implantes', 'Estética', 'Odontopediatría'];
            $dentistEmails = ['dentist1@clinica.com', 'dentist2@clinica.com', 'admin@dentify.com', 'receptionist@dentify.com', 'staff@dentify.com'];
            for ($i = 0; $i < 5; $i++) {
                $dentist = new Dentist();
                $dentist->setNombre("Dentista " . ($i + 1));
                $dentist->setApellidos("Apellido " . ($i + 1));
                $dentist->setEspecialidad($specialties[$i]);
                $dentist->setEmail($dentistEmails[$i]);
                $this->entityManager->persist($dentist);
                $dentists[] = $dentist;
            }
            $io->text('Dentists seeded.');

            // 4. Patients & Related info (datos ficticios realistas)
            $patients = [];
            $patientRows = PatientDemoSeed::pickRandom(8);
            $dientesDemo = ['16', '26', '36', '46', '11', '21', '31', '41'];

            foreach ($patientRows as $index => $row) {
                $patient = new Patient();
                $patient->setNombre($row['nombre']);
                $patient->setApellidos($row['apellidos']);
                $patient->setDni($row['dni']);
                $patient->setTelefono($row['telefono']);
                $patient->setEmail($row['email']);
                if (isset($row['nss'])) {
                    $patient->setNss($row['nss']);
                }
                if (isset($row['enfermedades'])) {
                    $patient->setEnfermedades($row['enfermedades']);
                }
                if (isset($row['alergias'])) {
                    $patient->setAlergias($row['alergias']);
                }
                if (isset($row['historial_clinico'])) {
                    $patient->setHistorialClinico($row['historial_clinico']);
                }
                if (isset($row['medical_flags'])) {
                    $patient->setMedicalFlags($row['medical_flags']);
                }
                if (isset($row['medical_notes'])) {
                    $patient->setMedicalNotes($row['medical_notes']);
                }
                $patient->setTerminosGeneralesAceptados($row['terminos_generales_aceptados'] ?? false);
                $this->entityManager->persist($patient);
                $patients[] = $patient;

                $nombreCompleto = $row['nombre'] . ' ' . $row['apellidos'];

                $history = new ClinicalHistory();
                $history->setDescripcion($row['historia_inicial'] ?? "Consulta inicial de {$nombreCompleto}.");
                $history->setTipo('Revision');
                $history->setPatient($patient);
                $this->entityManager->persist($history);

                $odontograma = new Odontograma();
                $odontograma->setPatient($patient);
                $this->entityManager->persist($odontograma);

                $patologia = new Patologia();
                $patologia->setDiente($dientesDemo[$index % \count($dientesDemo)]);
                $patologia->setCara('oclusal');
                $patologia->setEstado('caries');
                $patologia->setColor('#FF0000');
                $patologia->setTipoPatologia('patologia');
                $patologia->setOdontograma($odontograma);
                $this->entityManager->persist($patologia);

                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $row['apellidos']) ?? 'paciente');
                $radio = new Radiografia();
                $radio->setNombreArchivo(sprintf('OPG-%s-%s.jpg', $slug, $row['dni']));
                $radio->setRutaArchivo('/uploads/radiografias/dummy.jpg');
                $radio->setTipoMime('image/jpeg');
                $radio->setPatient($patient);
                $this->entityManager->persist($radio);

                $visita = new PrimeraVisita();
                $visita->setMotivoConsulta($row['motivo_primera_visita'] ?? 'Revisión general');
                $visita->setNivelDolor($row['nivel_dolor'] ?? 1);
                $visita->setPatient($patient);
                $this->entityManager->persist($visita);
            }
            $io->text(sprintf('%d pacientes con datos realistas insertados.', \count($patients)));

            // 5. Appointments (incl. citas de hoy con tratamiento de catálogo para probar sesión clínica)
            $treatmentRepo = $this->entityManager->getRepository(Treatment::class);
            $diagTreatment = $treatmentRepo->findOneBy(['name' => 'Diagnóstico']);
            $reconTreatment = $treatmentRepo->findOneBy(['name' => 'Reconstrucción dental']);
            if ($diagTreatment === null || $reconTreatment === null) {
                $io->warning(
                    'No se encontró el catálogo de tratamientos. Ejecuta las migraciones (doctrine:migrations:migrate) antes del seed.'
                );
            }

            $todaySlots = [
                ['hora' => '08:00:00', 'treatment' => $diagTreatment, 'estado' => 'confirmada'],
                ['hora' => '10:30:00', 'treatment' => $reconTreatment, 'estado' => 'programada'],
            ];
            foreach ($todaySlots as $idx => $slot) {
                $appointment = new Appointment();
                $appointment->setPaciente($patients[$idx]);
                $appointment->setOdontologo($dentists[$idx]);
                $appointment->setBox($boxes[$idx]);
                $appointment->setFecha(new \DateTime('today'));
                $appointment->setHoraInicio(new \DateTime($slot['hora']));
                $appointment->setDuracion($slot['treatment']?->getDefaultDurationMinutes() ?? 30);
                $appointment->setPrioridad('media');
                $appointment->setEstado($slot['estado']);
                if ($slot['treatment'] !== null) {
                    $appointment->setCatalogTreatment($slot['treatment']);
                    $appointment->setTreatment($slot['treatment']->getName());
                }
                $this->entityManager->persist($appointment);
            }

            for ($i = 0; $i < 3; $i++) {
                $appointment = new Appointment();
                $appointment->setPaciente($patients[$i + 2]);
                $appointment->setOdontologo($dentists[$i + 2]);
                $appointment->setBox($boxes[$i]);
                $appointment->setFecha(new \DateTime('+' . ($i + 1) . ' days'));
                $appointment->setHoraInicio(new \DateTime('11:00:00'));
                $appointment->setDuracion(30);
                $appointment->setPrioridad('media');
                $appointment->setEstado('programada');
                $this->entityManager->persist($appointment);
            }
            $io->text('Appointments seeded (2 hoy con tratamiento de catálogo).');

            // 6. Stock + protocolos (misma carga que app:seed-stock)
            $this->entityManager->flush();
            $stockResult = $this->stockProtocolSeeder->seed($io);
            $io->text(sprintf(
                '%d materiales y %d líneas de protocolo insertadas.',
                $stockResult['materials'],
                $stockResult['protocolLines']
            ));

            // 7. Pedagogical notices (FE #21 notification center + admin banners)
            $noticeAuthor = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'testuser'])
                ?? $adminUser;
            $now = new \DateTimeImmutable();
            $welcome = new PedagogicalNotice();
            $welcome->setTitle('Bienvenida al simulador clínico');
            $welcome->setBody('Revisa la agenda y el inventario. Los avisos del centro aparecerán aquí.');
            $welcome->setActive(true);
            $welcome->setPublishedAt($now->modify('-1 day'));
            $welcome->setExpiresAt(null);
            $welcome->setRequiresAck(true);
            $welcome->setCreatedAt($now->modify('-1 day'));
            $welcome->setUpdatedAt(null);
            $welcome->setCreatedBy($noticeAuthor);
            $this->entityManager->persist($welcome);

            $info = new PedagogicalNotice();
            $info->setTitle('Recordatorio de práctica');
            $info->setBody('Documenta cada intervención en la historia clínica del paciente.');
            $info->setActive(true);
            $info->setPublishedAt($now);
            $info->setExpiresAt(null);
            $info->setRequiresAck(false);
            $info->setCreatedAt($now);
            $info->setUpdatedAt(null);
            $info->setCreatedBy($noticeAuthor);
            $this->entityManager->persist($info);
            $io->text('Pedagogical notices seeded.');

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
