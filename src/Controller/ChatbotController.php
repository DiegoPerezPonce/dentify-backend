<?php

namespace App\Controller;

use App\Dto\ChatbotRequestDTO;
use App\Dto\ChatbotResponseDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/chatbot')]
class ChatbotController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}

    #[Route('', name: 'app_chatbot', methods: ['POST'])]
    public function index(#[MapRequestPayload] ChatbotRequestDTO $requestDTO): JsonResponse
    {
        $question = mb_strtolower($requestDTO->question);
        $answer = $this->getAnswer($question);

        return $this->json(new ChatbotResponseDTO($answer));
    }

    private function getAnswer(string $question): string
    {
        // Simple keyword-based logic
        if ($this->containsAny($question, ['horas', 'horario', 'abierto', 'tarde'])) {
            return "Nuestro horario es de lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00.";
        }

        if ($this->containsAny($question, ['servicios', 'tratamientos', 'hacen', 'especialidades'])) {
            return "Ofrecemos servicios de odontología general, ortodoncia, implantes, estética dental y odontopediatría.";
        }

        if ($this->containsAny($question, ['ubicacion', 'donde', 'direccion', 'llegar'])) {
            return "Estamos ubicados en la calle Falsa 123, en el centro de la ciudad.";
        }

        if ($this->containsAny($question, ['cita', 'reservar', 'turno', 'agendar'])) {
            return "Puedes reservar una cita llamando al 123-456-789 o mediante nuestro portal de pacientes en la web.";
        }

        if ($this->containsAny($question, ['hola', 'buenos dias', 'buenas tardes'])) {
            return "¡Hola! Soy el asistente virtual de Dentify. ¿En qué puedo ayudarte hoy?";
        }

        return "Lo siento, no he entendido tu pregunta. ¿Podrías reformularla o preguntarme sobre nuestros horarios, servicios o ubicación?";
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}
