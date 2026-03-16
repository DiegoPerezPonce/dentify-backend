<?php

namespace App\Command;

use App\Repository\AppointmentRepository;
use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-appointment-reminders',
    description: 'Sends email reminders for appointments scheduled for tomorrow',
)]
class SendAppointmentRemindersCommand extends Command
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d');

        $appointments = $this->appointmentRepository->findBy([
            'fecha' => new \DateTime($tomorrow),
            'estado' => 'programada',
        ]);

        if (empty($appointments)) {
            $io->info('No appointments scheduled for tomorrow.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($appointments as $appointment) {
            try {
                $this->emailService->sendAppointmentReminder($appointment);
                $count++;
            } catch (\Exception $e) {
                $io->error(sprintf('Failed to send reminder for appointment ID %d: %s', $appointment->getId(), $e->getMessage()));
            }
        }

        $io->success(sprintf('Sent %d reminders successfully.', $count));

        return Command::SUCCESS;
    }
}
