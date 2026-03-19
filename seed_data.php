<?php

use App\Kernel;
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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

require __DIR__.'/vendor/autoload.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.password_hasher');

echo "Seeding database...\n";

// 1. Users
$users = [];
$roles = [['ROLE_ADMIN'], ['ROLE_USER'], ['ROLE_USER'], ['ROLE_USER'], ['ROLE_USER']];
$usernames = ['admin', 'dentist1', 'dentist2', 'receptionist', 'staff'];

for ($i = 0; $i < 5; $i++) {
    $user = new User();
    $user->setEmail($usernames[$i] . "@dentify.com");
    $user->setNombreUsuario($usernames[$i]);
    $user->setRoles($roles[$i]);
    $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
    $entityManager->persist($user);
    $users[] = $user;
}

// 2. Boxes
$boxes = [];
for ($i = 1; $i <= 5; $i++) {
    $box = new Box();
    $box->setNombre("Box $i");
    $box->setEstado($i % 2 == 0 ? 'mantenimiento' : 'disponible');
    $entityManager->persist($box);
    $boxes[] = $box;
}

// 3. Dentists
$dentists = [];
$specialties = ['General', 'Ortodoncia', 'Implantes', 'Estética', 'Odontopediatría'];
for ($i = 0; $i < 5; $i++) {
    $dentist = new Dentist();
    $dentist->setNombre("Dr. " . $usernames[$i]);
    $dentist->setEspecialidad($specialties[$i]);
    $dentist->setTelefono("60000000" . $i);
    $dentist->setEmail($usernames[$i] . "@clinica.com");
    $dentist->addBox($boxes[$i]);
    $entityManager->persist($dentist);
    $dentists[] = $dentist;
}

// 4. Patients & Related info
$patients = [];
for ($i = 1; $i <= 5; $i++) {
    $patient = new Patient();
    $patient->setNombre("Paciente $i");
    $patient->setApellidos("Apellido $i");
    $patient->setDni(str_pad($i, 8, '0', STR_PAD_LEFT) . 'Z');
    $patient->setEmail("paciente$i@gmail.com");
    $patient->setTelefono("61111111" . $i);
    $entityManager->persist($patient);
    $patients[] = $patient;

    // Clinical History
    $history = new ClinicalHistory();
    $history->setDescripcion("Consulta inicial para el paciente $i.");
    $history->setTipo("Revision");
    $history->setPatient($patient);
    $entityManager->persist($history);

    // Odontograma
    $odontograma = new Odontograma();
    $odontograma->setPatient($patient);
    $entityManager->persist($odontograma);

    // Patologia
    $patologia = new Patologia();
    $patologia->setDiente("1" . $i);
    $patologia->setCara("oclusal");
    $patologia->setEstado("caries");
    $patologia->setColor("#FF0000");
    $patologia->setTipoPatologia("patologia");
    $patologia->setOdontograma($odontograma);
    $entityManager->persist($patologia);

    // Radiografia
    $radio = new Radiografia();
    $radio->setNombreArchivo("XRAY_$i.jpg");
    $radio->setRutaArchivo("/uploads/radiografias/dummy.jpg");
    $radio->setTipoMime("image/jpeg");
    $radio->setPatient($patient);
    $entityManager->persist($radio);

    // Primera Visita
    $visita = new PrimeraVisita();
    $visita->setMotivo("Revision anual");
    $visita->setPresupuesto(50.0);
    $visita->setPatient($patient);
    $entityManager->persist($visita);
}

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
    $entityManager->persist($appointment);
}

// 6. Stock Materials
for ($i = 1; $i <= 5; $i++) {
    $material = new StockMaterial();
    $material->setNombre("Material $i");
    $material->setCantidadActual(100.0);
    $material->setUnidad("unidades");
    $entityManager->persist($material);
}

$entityManager->flush();

echo "Seeding completed successfully!\n";
