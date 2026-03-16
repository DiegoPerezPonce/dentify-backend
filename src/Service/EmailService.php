<?php

namespace App\Service;

use App\Entity\Appointment;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    private string $fromEmail = 'noreply@dentify.com';
    private string $fromName = 'Dentify Clinic';

    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendAppointmentConfirmation(Appointment $appointment): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($appointment->getPaciente()->getEmail())
            ->subject('Confirmación de Cita - Dentify')
            ->htmlTemplate('emails/appointment_confirmation.html.twig')
            ->context([
                'appointment' => $appointment,
                'patient' => $appointment->getPaciente(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentCancellation(Appointment $appointment): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($appointment->getPaciente()->getEmail())
            ->subject('Cancelación de Cita - Dentify')
            ->htmlTemplate('emails/appointment_cancellation.html.twig')
            ->context([
                'appointment' => $appointment,
                'patient' => $appointment->getPaciente(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentReminder(Appointment $appointment): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($appointment->getPaciente()->getEmail())
            ->subject('Recordatorio de Cita - Dentify')
            ->htmlTemplate('emails/appointment_reminder.html.twig')
            ->context([
                'appointment' => $appointment,
                'patient' => $appointment->getPaciente(),
            ]);

        $this->mailer->send($email);
    }
}
