<?php

namespace App\Controller;

use App\Dto\PatientCreateDTO;
use App\Dto\PatientUpdateDTO;
use App\Entity\Patient;
use App\Entity\ClinicalHistory;
use App\Mapper\PatientMapper;
use App\Mapper\ClinicalHistoryMapper;
use App\Mapper\OdontogramaMapper;
use App\Mapper\RadiografiaMapper;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/patients')]
#[IsGranted('ROLE_USER')]
class PatientController extends AbstractController
{
    public function __construct(
        private PatientRepository $patientRepository,
        private PatientMapper $patientMapper,
        private ClinicalHistoryMapper $clinicalHistoryMapper,
        private OdontogramaMapper $odontogramaMapper,
        private RadiografiaMapper $radiografiaMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_patient_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $searchTerm = $request->query->get('q');
        
        if ($searchTerm) {
            $patients = $this->patientRepository->findBySearchTerm($searchTerm);
        } else {
            $patients = $this->patientRepository->findAll();
        }

        $responseDtos = array_map(
            fn(Patient $patient) => $this->patientMapper->toResponseDto($patient),
            $patients
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_patient_get', methods: ['GET'])]
    public function get(Patient $patient): JsonResponse
    {
        return $this->json($this->patientMapper->toResponseDto($patient));
    }

    #[Route('/{id}/history', name: 'app_patient_history_list', methods: ['GET'])]
    public function history(Patient $patient): JsonResponse
    {
        $histories = $patient->getClinicalHistories();
        $responseDtos = array_map(
            fn(ClinicalHistory $history) => $this->clinicalHistoryMapper->toResponseDto($history),
            $histories->toArray()
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}/odontogramas', name: 'app_patient_odontograma_list', methods: ['GET'])]
    public function odontogramas(Patient $patient): JsonResponse
    {
        $odontogramas = $patient->getOdontogramas();
        $responseDtos = array_map(
            fn(\App\Entity\Odontograma $odontograma) => $this->odontogramaMapper->toResponseDto($odontograma),
            $odontogramas->toArray()
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}/radiografias', name: 'app_patient_radiografias_list', methods: ['GET'])]
    public function radiografias(Patient $patient): JsonResponse
    {
        $radiografias = $patient->getRadiografias();
        $responseDtos = array_map(
            fn(\App\Entity\Radiografia $radiografia) => $this->radiografiaMapper->toResponseDto($radiografia),
            $radiografias->toArray()
        );

        return $this->json($responseDtos);
    }


    #[Route('', name: 'app_patient_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] PatientCreateDTO $patientCreateDTO): JsonResponse
    {
        $patient = $this->patientMapper->toEntity($patientCreateDTO);
        
        $this->entityManager->persist($patient);
        $this->entityManager->flush();

        return $this->json(
            $this->patientMapper->toResponseDto($patient),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_patient_update', methods: ['PUT', 'PATCH'])]
    public function update(Patient $patient, #[MapRequestPayload] PatientUpdateDTO $patientUpdateDTO): JsonResponse
    {
        $patient = $this->patientMapper->updateEntity($patient, $patientUpdateDTO);
        
        $this->entityManager->flush();

        return $this->json($this->patientMapper->toResponseDto($patient));
    }

    #[Route('/{id}', name: 'app_patient_delete', methods: ['DELETE'])]
    public function delete(Patient $patient): JsonResponse
    {
        $this->entityManager->remove($patient);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
