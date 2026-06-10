<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Box;
use App\Entity\Dentist;
use App\Entity\Patient;
use App\Repository\AppointmentRepository;
use App\Repository\BoxRepository;
use App\Repository\DentistRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-stress',
    description: 'Inserta pacientes y citas masivos para pruebas de estrés (no borra datos existentes).',
)]
class SeedStressCommand extends Command
{
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /** @var list<string> */
    private const FIRST_NAMES = [
        'Ana', 'Carlos', 'Elena', 'Javier', 'Lucía', 'Miguel', 'Paula', 'Raúl',
        'Sara', 'David', 'María', 'Pablo', 'Laura', 'Diego', 'Carmen', 'Hugo',
        'Isabel', 'Rubén', 'Nuria', 'Álvaro', 'Claudia', 'Iván', 'Beatriz', 'Óscar',
    ];

    /** @var list<string> */
    private const LAST_NAMES = [
        'García', 'Martínez', 'López', 'Sánchez', 'González', 'Rodríguez', 'Fernández',
        'Pérez', 'Gómez', 'Ruiz', 'Díaz', 'Moreno', 'Álvarez', 'Romero', 'Navarro',
        'Torres', 'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Serrano', 'Molina', 'Suárez',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PatientRepository $patientRepository,
        private AppointmentRepository $appointmentRepository,
        private DentistRepository $dentistRepository,
        private BoxRepository $boxRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('patients', 'p', InputOption::VALUE_REQUIRED, 'Pacientes a insertar', '1000')
            ->addOption('appointments', 'a', InputOption::VALUE_REQUIRED, 'Citas a insertar', '3000')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Flush cada N registros', '50')
            ->addOption('skip-benchmark', null, InputOption::VALUE_NONE, 'Omitir consultas de referencia al final');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $patientCount = max(0, (int) $input->getOption('patients'));
        $appointmentCount = max(0, (int) $input->getOption('appointments'));
        $batchSize = max(1, (int) $input->getOption('batch-size'));
        $skipBenchmark = (bool) $input->getOption('skip-benchmark');

        $io->title('Pruebas de estrés — carga masiva');
        $io->note([
            'Este comando NO borra usuarios ni datos existentes.',
            'Solo inserta pacientes y citas adicionales con datos sintéticos.',
            'Usar únicamente en entorno de desarrollo (dentify_db).',
        ]);

        if ($patientCount === 0 && $appointmentCount === 0) {
            $io->warning('Indica --patients y/o --appointments mayor que 0.');

            return Command::FAILURE;
        }

        $dentists = $this->dentistRepository->findAll();
        $boxes = array_values(array_filter(
            $this->boxRepository->findAll(),
            static fn (Box $box) => $box->getEstado() !== 'mantenimiento',
        ));

        if ($dentists === []) {
            $io->text('No hay odontólogos; creando 3 de prueba...');
            $dentists = $this->createMinimalDentists(3);
        }
        if ($boxes === []) {
            $io->text('No hay cajas disponibles; creando 3 de prueba...');
            $boxes = $this->createMinimalBoxes(3);
            $this->entityManager->flush();
        }

        $conn = $this->entityManager->getConnection();
        $baseline = $this->countRows($conn);
        $io->section('Estado inicial');
        $io->listing([
            sprintf('Pacientes: %d', $baseline['patient']),
            sprintf('Citas: %d', $baseline['appointment']),
            sprintf('Odontólogos: %d', \count($dentists)),
            sprintf('Cajas disponibles: %d', \count($boxes)),
        ]);

        $runSuffix = (string) (int) (microtime(true) * 1000);
        $insertedPatients = 0;
        $insertedAppointments = 0;

        if ($patientCount > 0) {
            $io->section(sprintf('Insertando %d pacientes (batch %d)...', $patientCount, $batchSize));
            $patientStart = microtime(true);
            $insertedPatients = $this->insertPatients($patientCount, $batchSize, $runSuffix, $io);
            $patientElapsed = microtime(true) - $patientStart;
            $io->success(sprintf(
                '%d pacientes insertados en %.2f s (%.0f reg/s).',
                $insertedPatients,
                $patientElapsed,
                $insertedPatients > 0 ? $insertedPatients / $patientElapsed : 0,
            ));
        }

        if ($appointmentCount > 0) {
            $io->section(sprintf('Insertando %d citas (batch %d)...', $appointmentCount, $batchSize));
            $patientIds = array_map(
                'intval',
                $conn->fetchFirstColumn('SELECT id FROM patient ORDER BY id'),
            );
            if ($patientIds === []) {
                $io->error('No hay pacientes en la base de datos. Inserta pacientes primero.');

                return Command::FAILURE;
            }

            $appointmentStart = microtime(true);
            $insertedAppointments = $this->insertAppointments(
                $appointmentCount,
                $batchSize,
                $patientIds,
                $dentists,
                $boxes,
                $io,
            );
            $appointmentElapsed = microtime(true) - $appointmentStart;
            $io->success(sprintf(
                '%d citas insertadas en %.2f s (%.0f reg/s).',
                $insertedAppointments,
                $appointmentElapsed,
                $insertedAppointments > 0 ? $insertedAppointments / $appointmentElapsed : 0,
            ));
        }

        $final = $this->countRows($conn);
        $io->section('Estado final');
        $io->table(
            ['Tabla', 'Antes', 'Después', 'Δ'],
            [
                ['patient', (string) $baseline['patient'], (string) $final['patient'], '+' . ($final['patient'] - $baseline['patient'])],
                ['appointment', (string) $baseline['appointment'], (string) $final['appointment'], '+' . ($final['appointment'] - $baseline['appointment'])],
            ],
        );

        if (!$skipBenchmark) {
            $this->runBenchmarks($io);
        }

        $io->success('Carga de estrés completada. Mide tiempos en Postman o con verify-api-flows.mjs.');

        return Command::SUCCESS;
    }

    /**
     * @return array{patient: int, appointment: int}
     */
    private function countRows(\Doctrine\DBAL\Connection $conn): array
    {
        return [
            'patient' => (int) $conn->fetchOne('SELECT COUNT(*) FROM patient'),
            'appointment' => (int) $conn->fetchOne('SELECT COUNT(*) FROM appointment'),
        ];
    }

    private function insertPatients(int $count, int $batchSize, string $runSuffix, SymfonyStyle $io): int
    {
        $inserted = 0;
        $progress = $io->createProgressBar($count);
        $progress->start();

        for ($i = 0; $i < $count; ++$i) {
            $patient = new Patient();
            $nombre = self::FIRST_NAMES[$i % \count(self::FIRST_NAMES)];
            $apellidos = self::LAST_NAMES[($i * 3) % \count(self::LAST_NAMES)]
                . ' '
                . self::LAST_NAMES[($i * 7 + 1) % \count(self::LAST_NAMES)];

            $patient->setNombre($nombre);
            $patient->setApellidos($apellidos);
            $patient->setDni($this->generateUniqueDni($runSuffix, $i));
            $patient->setEmail(sprintf('stress-%s-%05d@dentify-stress.test', $runSuffix, $i));
            $patient->setTelefono($this->generatePhone($i));
            $patient->setTerminosGeneralesAceptados($i % 3 === 0);
            if ($i % 5 === 0) {
                $patient->setHistorialClinico('Paciente de prueba de estrés — datos sintéticos.');
            }

            $this->entityManager->persist($patient);
            ++$inserted;

            if ($inserted % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progress->advance($batchSize);
            }
        }

        $remainder = $inserted % $batchSize;
        if ($remainder !== 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $progress->advance($remainder);
        }

        $progress->finish();
        $io->newLine(2);

        return $inserted;
    }

    /**
     * @param list<int> $patientIds
     * @param list<Dentist> $dentists
     * @param list<Box> $boxes
     */
    private function insertAppointments(
        int $count,
        int $batchSize,
        array $patientIds,
        array $dentists,
        array $boxes,
        SymfonyStyle $io,
    ): int {
        $inserted = 0;
        $progress = $io->createProgressBar($count);
        $progress->start();

        $estados = ['programada', 'confirmada', 'completada', 'cancelada'];
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endTs = (new \DateTimeImmutable('2026-12-31'))->getTimestamp();
        $startTs = $startDate->getTimestamp();
        $dentistIds = array_values(array_filter(array_map(
            static fn (Dentist $dentist): ?int => $dentist->getId(),
            $dentists,
        )));
        $boxIds = array_values(array_filter(array_map(
            static fn (Box $box): ?int => $box->getId(),
            $boxes,
        )));
        $patientCount = \count($patientIds);
        $dentistCount = \count($dentistIds);
        $boxCount = \count($boxIds);

        for ($i = 0; $i < $count; ++$i) {
            $patientRef = $this->entityManager->getReference(Patient::class, $patientIds[$i % $patientCount]);
            $dentistRef = $this->entityManager->getReference(Dentist::class, $dentistIds[$i % $dentistCount]);
            $boxRef = $this->entityManager->getReference(Box::class, $boxIds[$i % $boxCount]);

            $dayOffset = (int) (($startTs + ($i * 37) % max(1, $endTs - $startTs)) - $startTs);
            $fecha = $startDate->modify('+' . $dayOffset . ' days');
            $hour = 8 + ($i % 10);
            $minute = ($i % 4) * 15;

            $appointment = new Appointment();
            $appointment->setPaciente($patientRef);
            $appointment->setOdontologo($dentistRef);
            $appointment->setBox($boxRef);
            $appointment->setFecha(\DateTime::createFromImmutable($fecha));
            $appointment->setHoraInicio(new \DateTime(sprintf('%02d:%02d:00', $hour, $minute)));
            $appointment->setDuracion([30, 45, 60][$i % 3]);
            $appointment->setPrioridad(['baja', 'media', 'alta'][$i % 3]);
            $appointment->setEstado($estados[$i % \count($estados)]);

            $this->entityManager->persist($appointment);
            ++$inserted;

            if ($inserted % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progress->advance($batchSize);
            }
        }

        $remainder = $inserted % $batchSize;
        if ($remainder !== 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $progress->advance($remainder);
        }

        $progress->finish();
        $io->newLine(2);

        return $inserted;
    }

    private function runBenchmarks(SymfonyStyle $io): void
    {
        $io->section('Consultas de referencia (ms)');

        $conn = $this->entityManager->getConnection();

        $sqlCountPatient = $this->measureMs(static fn () => $conn->fetchOne('SELECT COUNT(*) FROM patient'));
        $sqlSearchPatient = $this->measureMs(static fn () => $conn->fetchAllAssociative(
            "SELECT id FROM patient WHERE nombre LIKE '%gar%' OR apellidos LIKE '%gar%' OR dni LIKE '%gar%' LIMIT 20",
        ));

        $paginated = $this->measureMs(fn () => $this->patientRepository->findPaginated(null, 1, 20));
        $paginatedSearch = $this->measureMs(fn () => $this->patientRepository->findPaginated('garcia', 1, 20));

        $start = new \DateTimeImmutable('2025-01-01');
        $end = new \DateTimeImmutable('2025-12-31');
        $appointments = $this->measureMs(fn () => $this->appointmentRepository->findList(
            ['startDate' => $start, 'endDate' => $end],
            0,
            50,
        ));

        $io->table(
            ['Consulta', 'Tiempo (ms)', 'Detalle'],
            [
                ['SQL COUNT patient', (string) $sqlCountPatient['ms'], ''],
                ['SQL LIKE patient (gar)', (string) $sqlSearchPatient['ms'], '20 filas máx.'],
                ['ORM pacientes pág. 1', (string) $paginated['ms'], sprintf('%d total', $paginated['result']['total'] ?? 0)],
                ['ORM pacientes search=garcia', (string) $paginatedSearch['ms'], sprintf('%d total', $paginatedSearch['result']['total'] ?? 0)],
                ['ORM citas 2025 (50 filas)', (string) $appointments['ms'], sprintf('%d total', $appointments['result'][1] ?? 0)],
            ],
        );
    }

    /**
     * @template T
     * @param callable(): T $fn
     * @return array{ms: float, result: T}
     */
    private function measureMs(callable $fn): array
    {
        $start = microtime(true);
        $result = $fn();
        $ms = round((microtime(true) - $start) * 1000, 2);

        return ['ms' => $ms, 'result' => $result];
    }

    private function generateUniqueDni(string $runSuffix, int $index): string
    {
        $seed = (int) substr(preg_replace('/\D/', '', $runSuffix) ?? '0', -7) * 100000 + $index;
        $num = 10000000 + ($seed % 90000000);

        return sprintf('%08d%s', $num, self::DNI_LETTERS[$num % 23]);
    }

    private function generatePhone(int $index): string
    {
        $a = 600 + ($index % 50);
        $b = ($index * 13) % 100;
        $c = ($index * 29) % 100;
        $d = ($index * 41) % 100;

        return sprintf('%d %02d %02d %02d', $a, $b, $c, $d);
    }

    /**
     * @return list<Dentist>
     */
    private function createMinimalDentists(int $count): array
    {
        $created = [];
        for ($i = 1; $i <= $count; ++$i) {
            $dentist = new Dentist();
            $dentist->setNombre('Stress Dentista ' . $i);
            $dentist->setApellidos('Prueba');
            $dentist->setEspecialidad('General');
            $dentist->setEmail(sprintf('stress-dentist-%d@dentify-stress.test', $i));
            $this->entityManager->persist($dentist);
            $created[] = $dentist;
        }
        $this->entityManager->flush();

        return $created;
    }

    /**
     * @return list<Box>
     */
    private function createMinimalBoxes(int $count): array
    {
        $created = [];
        for ($i = 1; $i <= $count; ++$i) {
            $box = new Box();
            $box->setNombre('Stress Box ' . $i);
            $box->setEstado('disponible');
            $this->entityManager->persist($box);
            $created[] = $box;
        }

        return $created;
    }
}
