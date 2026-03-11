<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Radiografia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/radiografias')]
#[IsGranted('ROLE_USER')]
class RadiografiaController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    #[Route('/upload', name: 'app_radiografias_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $patientId = $request->request->get('patient_id');
        if (!$patientId) {
            return $this->json(['error' => 'Patient ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $patient = $this->entityManager->getRepository(Patient::class)->find($patientId);
        if (!$patient) {
            return $this->json(['error' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var UploadedFile[] $files */
        $files = $request->files->get('files');
        if (!$files || !is_array($files)) {
            return $this->json(['error' => 'No files uploaded or invalid format'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $uploadedData = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {
                return $this->json([
                    'error' => sprintf('Invalid file type: %s. Allowed: %s', $file->getClientOriginalName(), implode(', ', $allowedMimeTypes))
                ], Response::HTTP_BAD_REQUEST);
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/radiografias',
                    $newFilename
                );
            } catch (FileException $e) {
                return $this->json(['error' => 'Failed to upload file: '.$file->getClientOriginalName()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $radiografia = new Radiografia();
            $radiografia->setNombreArchivo($file->getClientOriginalName());
            $radiografia->setRutaArchivo('/uploads/radiografias/'.$newFilename);
            $radiografia->setTipoMime($file->getClientMimeType());
            $radiografia->setPatient($patient);

            $this->entityManager->persist($radiografia);
            $uploadedData[] = [
                'id' => null, // Will be set after flush, but we'll return the object in a real app
                'nombre' => $radiografia->getNombreArchivo(),
                'ruta' => $radiografia->getRutaArchivo()
            ];
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Files uploaded successfully', 'data' => $uploadedData], Response::HTTP_CREATED);
    }
}
