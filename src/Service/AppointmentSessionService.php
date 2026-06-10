<?php

namespace App\Service;

use App\Dto\AppointmentSessionMaterialInputDTO;
use App\Dto\AppointmentSessionMaterialLineUpdateDTO;
use App\Dto\AppointmentSessionResponseDTO;
use App\Entity\Appointment;
use App\Entity\AppointmentSession;
use App\Entity\AppointmentSessionMaterial;
use App\Entity\StockMaterial;
use App\Mapper\AppointmentSessionMapper;
use App\Repository\AppointmentRepository;
use App\Repository\AppointmentSessionRepository;
use App\Repository\ProtocoloTratamientoRepository;
use App\Repository\StockMaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppointmentSessionService
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private AppointmentSessionRepository $sessionRepository,
        private ProtocoloTratamientoRepository $protocoloRepository,
        private StockMaterialRepository $stockMaterialRepository,
        private AppointmentSessionMapper $mapper,
        private EntityManagerInterface $entityManager,
    ) {}

    public function getSession(int $appointmentId): AppointmentSessionResponseDTO
    {
        $appointment = $this->requireAppointment($appointmentId);
        $session = $this->sessionRepository->findOneByAppointment($appointment);
        if ($session === null) {
            throw new NotFoundHttpException('No hay sesión clínica para esta cita.');
        }

        return $this->mapper->toResponseDto($session, $appointment);
    }

    public function startSession(int $appointmentId): AppointmentSessionResponseDTO
    {
        $appointment = $this->requireAppointment($appointmentId);
        $existing = $this->sessionRepository->findOneByAppointment($appointment);

        if ($existing !== null) {
            if ($existing->getStatus() === AppointmentSession::STATUS_COMPLETED) {
                return $this->mapper->toResponseDto($existing, $appointment);
            }

            return $this->mapper->toResponseDto($existing, $appointment);
        }

        $session = new AppointmentSession();
        $session->setAppointment($appointment);
        $session->setStatus(AppointmentSession::STATUS_IN_PROGRESS);
        $session->setStartedAt(new \DateTime());

        $this->preloadMaterialsFromProtocol($session, $appointment);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $this->mapper->toResponseDto($session, $appointment);
    }

    public function addMaterial(int $appointmentId, AppointmentSessionMaterialInputDTO $input): AppointmentSessionResponseDTO
    {
        $session = $this->requireActiveSession($appointmentId);
        $material = $this->requireStockMaterial($input->stock_material_id);
        $qty = (float) ($input->cantidad_usada ?? $input->cantidad_planificada ?? 1);
        if ($qty <= 0) {
            throw new BadRequestHttpException('La cantidad debe ser mayor que cero.');
        }

        $planned = (float) ($input->cantidad_planificada ?? $qty);
        $line = new AppointmentSessionMaterial();
        $line->setMaterial($material);
        $line->setCantidadPlanificada($planned);
        $line->setCantidadUsada($qty);
        $line->setFromProtocol(false);
        $session->addMaterial($line);

        $this->entityManager->flush();

        return $this->mapper->toResponseDto($session, $session->getAppointment());
    }

    /**
     * @param AppointmentSessionMaterialLineUpdateDTO[] $lines
     */
    public function syncMaterials(int $appointmentId, array $lines): AppointmentSessionResponseDTO
    {
        $session = $this->requireActiveSession($appointmentId);
        $appointment = $session->getAppointment();
        if ($appointment === null) {
            throw new NotFoundHttpException('Cita no encontrada.');
        }

        /** @var array<int, AppointmentSessionMaterial> $existingById */
        $existingById = [];
        foreach ($session->getMaterials() as $existing) {
            if ($existing->isStockReduced() || $existing->getId() === null) {
                continue;
            }
            $existingById[$existing->getId()] = $existing;
        }

        $keptIds = [];
        foreach ($lines as $row) {
            if ($row->stock_material_id === null || $row->cantidad_usada === null || $row->cantidad_usada <= 0) {
                continue;
            }

            $qtyUsed = (float) $row->cantidad_usada;
            $qtyPlanned = (float) ($row->cantidad_planificada ?? $qtyUsed);

            if ($row->id !== null && isset($existingById[$row->id])) {
                $line = $existingById[$row->id];
                $line->setCantidadUsada($qtyUsed);
                $line->setCantidadPlanificada($qtyPlanned);
                $keptIds[$row->id] = true;
                continue;
            }

            $material = $this->requireStockMaterial($row->stock_material_id);
            $line = new AppointmentSessionMaterial();
            $line->setMaterial($material);
            $line->setCantidadUsada($qtyUsed);
            $line->setCantidadPlanificada($qtyPlanned);
            $line->setFromProtocol(false);
            $session->addMaterial($line);
        }

        foreach ($existingById as $id => $orphan) {
            if (!isset($keptIds[$id])) {
                $session->removeMaterial($orphan);
                $this->entityManager->remove($orphan);
            }
        }

        $this->entityManager->flush();

        $fresh = $this->sessionRepository->findOneByAppointment($appointment);

        return $this->mapper->toResponseDto($fresh ?? $session, $appointment);
    }

    public function removeMaterialLine(int $appointmentId, int $lineId): AppointmentSessionResponseDTO
    {
        $session = $this->requireActiveSession($appointmentId);
        foreach ($session->getMaterials() as $line) {
            if ($line->getId() === $lineId) {
                if ($line->isStockReduced()) {
                    throw new ConflictHttpException('No se puede quitar un material ya descontado del stock.');
                }
                $session->removeMaterial($line);
                $this->entityManager->remove($line);
                $this->entityManager->flush();

                return $this->mapper->toResponseDto($session, $session->getAppointment());
            }
        }

        throw new NotFoundHttpException('Línea de material no encontrada.');
    }

    public function completeSession(int $appointmentId, ?string $notes = null): AppointmentSessionResponseDTO
    {
        $session = $this->requireActiveSession($appointmentId);
        $appointment = $session->getAppointment();
        if ($appointment === null) {
            throw new NotFoundHttpException('Cita no encontrada.');
        }

        if ($notes !== null) {
            $session->setNotes(trim($notes) !== '' ? trim($notes) : null);
        }

        $this->entityManager->beginTransaction();
        try {
            foreach ($session->getMaterials() as $line) {
                if ($line->isStockReduced()) {
                    continue;
                }

                $qty = $line->getCantidadUsada();
                if ($qty <= 0) {
                    continue;
                }

                $stock = $line->getMaterial();
                if ($stock === null) {
                    throw new BadRequestHttpException('Material de stock inválido.');
                }

                if ($stock->getCantidadActual() < $qty) {
                    throw new ConflictHttpException(sprintf(
                        'Stock insuficiente para «%s». Disponible: %s, necesario: %s',
                        $stock->getNombre(),
                        $stock->getCantidadActual(),
                        $qty
                    ));
                }

                $stock->setCantidadActual($stock->getCantidadActual() - $qty);
                $line->setStockReduced(true);
            }

            $session->setStatus(AppointmentSession::STATUS_COMPLETED);
            $session->setCompletedAt(new \DateTime());
            $appointment->setEstado('completada');

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->mapper->toResponseDto($session, $appointment);
    }

    private function preloadMaterialsFromProtocol(AppointmentSession $session, Appointment $appointment): void
    {
        $treatmentName = $this->resolveTreatmentName($appointment);
        if ($treatmentName === null || $treatmentName === '') {
            return;
        }

        $protocolRows = $this->protocoloRepository->findByTreatmentName($treatmentName);
        /** @var array<int, array{material: StockMaterial, cantidad: float, notes: ?string}> $merged */
        $merged = [];

        foreach ($protocolRows as $row) {
            $material = $row->getMaterial();
            if ($material === null || $material->getId() === null) {
                continue;
            }
            $mid = $material->getId();
            if (!isset($merged[$mid])) {
                $merged[$mid] = [
                    'material' => $material,
                    'cantidad' => 0.0,
                    'notes' => $row->getNotasPasoAPaso(),
                ];
            }
            $merged[$mid]['cantidad'] += (float) $row->getCantidadNecesaria();
        }

        foreach ($merged as $item) {
            $line = new AppointmentSessionMaterial();
            $line->setMaterial($item['material']);
            $qty = max(0.0, $item['cantidad']);
            $line->setCantidadPlanificada($qty);
            $line->setCantidadUsada($qty > 0 ? $qty : 1.0);
            $line->setFromProtocol(true);
            $line->setProtocolNotes($item['notes']);
            $session->addMaterial($line);
        }
    }

    private function resolveTreatmentName(Appointment $appointment): ?string
    {
        $catalog = $appointment->getCatalogTreatment();
        if ($catalog !== null && $catalog->getName() !== null && trim($catalog->getName()) !== '') {
            return trim($catalog->getName());
        }

        $text = $appointment->getTreatment();

        return $text !== null && trim($text) !== '' ? trim($text) : null;
    }

    private function requireAppointment(int $appointmentId): Appointment
    {
        $appointment = $this->appointmentRepository->findOneForSession($appointmentId);
        if ($appointment === null) {
            throw new NotFoundHttpException('Cita no encontrada.');
        }

        return $appointment;
    }

    private function requireActiveSession(int $appointmentId): AppointmentSession
    {
        $appointment = $this->requireAppointment($appointmentId);
        $session = $this->sessionRepository->findOneByAppointment($appointment);
        if ($session === null) {
            throw new NotFoundHttpException('Inicia la sesión clínica antes de continuar.');
        }
        if ($session->getStatus() === AppointmentSession::STATUS_COMPLETED) {
            throw new ConflictHttpException('La sesión ya está completada.');
        }

        return $session;
    }

    private function requireStockMaterial(?int $id): StockMaterial
    {
        if ($id === null || $id <= 0) {
            throw new BadRequestHttpException('stock_material_id no válido.');
        }
        $material = $this->stockMaterialRepository->find($id);
        if ($material === null) {
            throw new NotFoundHttpException('Material de stock no encontrado.');
        }

        return $material;
    }
}
