<?php

namespace App\Controller;

use App\Domain\Schedule\DentistWorkShift;
use App\Dto\ClinicScheduleSettingsDTO;
use App\Dto\WeeklyScheduleDayInputDTO;
use App\Dto\WeeklyScheduleSaveDTO;
use App\Entity\Dentist;
use App\Entity\DentistWeeklySchedule;
use App\Entity\User;
use App\Mapper\ScheduleMapper;
use App\Repository\ClinicScheduleSettingsRepository;
use App\Repository\DentistRepository;
use App\Repository\DentistWeeklyScheduleRepository;
use App\Repository\UserRepository;
use App\Service\ClinicHoursValidator;
use App\Service\DentistScheduleAvailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/schedule')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private ClinicScheduleSettingsRepository $clinicSettingsRepository,
        private DentistWeeklyScheduleRepository $weeklyScheduleRepository,
        private DentistRepository $dentistRepository,
        private UserRepository $userRepository,
        private ScheduleMapper $scheduleMapper,
        private EntityManagerInterface $entityManager,
        private DentistScheduleAvailabilityService $dentistAvailabilityService,
        private ClinicHoursValidator $clinicHoursValidator
    ) {}

    #[Route('/clinic', name: 'app_schedule_clinic_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getClinicSettings(): JsonResponse
    {
        $settings = $this->clinicSettingsRepository->getOrCreateDefault();

        return $this->json($this->scheduleMapper->clinicToDto($settings));
    }

    #[Route('/clinic', name: 'app_schedule_clinic_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateClinicSettings(#[MapRequestPayload] ClinicScheduleSettingsDTO $dto): JsonResponse
    {
        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $settings->setOpenTime($this->normalizeTime($dto->openTime, 'openTime'));
        $settings->setCloseTime($this->normalizeTime($dto->closeTime, 'closeTime'));
        $settings->setMorningEndTime($this->normalizeTime($dto->morningEndTime, 'morningEndTime'));
        $settings->setAfternoonStartTime($this->normalizeTime($dto->afternoonStartTime, 'afternoonStartTime'));
        $this->entityManager->flush();

        return $this->json($this->scheduleMapper->clinicToDto($settings));
    }

    #[Route('/dentists-available', name: 'app_schedule_dentists_available', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listAvailableDentists(Request $request): JsonResponse
    {
        $startRaw = $request->query->getString('startDateTime');
        if ($startRaw === '') {
            throw new BadRequestHttpException('startDateTime is required.');
        }

        $duration = (int) $request->query->get('duration', '30');
        if ($duration < 1) {
            throw new BadRequestHttpException('Invalid duration.');
        }

        $excludeRaw = $request->query->get('excludeAppointmentId');
        $excludeId = is_numeric($excludeRaw) ? (int) $excludeRaw : null;

        try {
            $start = new \DateTimeImmutable($startRaw);
        } catch (\Exception) {
            throw new BadRequestHttpException('Invalid startDateTime.');
        }

        $clinicHoursError = $this->clinicHoursValidator->validateWindow($start, $duration);
        if ($clinicHoursError !== null) {
            return $this->json([
                'dentistIds' => [],
                'message' => $clinicHoursError,
            ]);
        }

        $dentistIds = $this->dentistAvailabilityService->findAvailableDentistIds(
            $start,
            $duration,
            $excludeId
        );

        return $this->json(['dentistIds' => $dentistIds]);
    }

    #[Route('/boxes-available', name: 'app_schedule_boxes_available', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listAvailableBoxes(Request $request): JsonResponse
    {
        $startRaw = $request->query->getString('startDateTime');
        if ($startRaw === '') {
            throw new BadRequestHttpException('startDateTime is required.');
        }

        $duration = (int) $request->query->get('duration', '30');
        if ($duration < 1) {
            throw new BadRequestHttpException('Invalid duration.');
        }

        $excludeRaw = $request->query->get('excludeAppointmentId');
        $excludeId = is_numeric($excludeRaw) ? (int) $excludeRaw : null;

        try {
            $start = new \DateTimeImmutable($startRaw);
        } catch (\Exception) {
            throw new BadRequestHttpException('Invalid startDateTime.');
        }

        $clinicHoursError = $this->clinicHoursValidator->validateWindow($start, $duration);
        if ($clinicHoursError !== null) {
            return $this->json([
                'boxIds' => [],
                'message' => $clinicHoursError,
            ]);
        }

        $boxIds = $this->dentistAvailabilityService->findAvailableBoxIds(
            $start,
            $duration,
            $excludeId
        );

        return $this->json(['boxIds' => $boxIds]);
    }

    #[Route('/dentists/{id}/weekly', name: 'app_schedule_dentist_weekly_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getDentistWeekly(Dentist $dentist): JsonResponse
    {
        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $rows = $this->weeklyScheduleRepository->findByDentist($dentist);

        return $this->json($this->scheduleMapper->toWeeklyResponse($dentist, $settings, $rows));
    }

    #[Route('/dentists/{id}/weekly', name: 'app_schedule_dentist_weekly_save', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function saveDentistWeekly(
        Request $request,
        Dentist $dentist,
        #[MapRequestPayload] WeeklyScheduleSaveDTO $dto
    ): JsonResponse {
        $dto->days = $this->resolveDaysFromRequest($request, $dto->days);

        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $existing = $this->weeklyScheduleRepository->findByDentist($dentist);
        $indexed = [];
        foreach ($existing as $row) {
            $indexed[$row->getWeekday()] = $row;
        }

        $seen = [];
        foreach ($dto->days as $dayInput) {
            if ($dayInput->weekday < 1 || $dayInput->weekday > 6) {
                throw new BadRequestHttpException('Invalid weekday.');
            }
            if (isset($seen[$dayInput->weekday])) {
                throw new BadRequestHttpException('Duplicate weekday.');
            }
            $seen[$dayInput->weekday] = true;
            if (!DentistWorkShift::isValid($dayInput->shift)) {
                throw new BadRequestHttpException(sprintf('Invalid shift for weekday %d.', $dayInput->weekday));
            }

            $row = $indexed[$dayInput->weekday] ?? new DentistWeeklySchedule();
            if ($row->getId() === null) {
                $row->setDentist($dentist);
                $this->entityManager->persist($row);
            }
            $row->setWeekday($dayInput->weekday);
            $row->setShift($dayInput->shift);
            $indexed[$dayInput->weekday] = $row;
        }

        $this->entityManager->flush();
        $rows = $this->weeklyScheduleRepository->findByDentist($dentist);

        return $this->json($this->scheduleMapper->toWeeklyResponse($dentist, $settings, $rows));
    }

    #[Route('/me/weekly', name: 'app_schedule_me_weekly', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyWeekly(): JsonResponse
    {
        $dentist = $this->resolveDentistForCurrentUser();
        if ($dentist === null) {
            throw new AccessDeniedHttpException('No dentist profile linked to this user.');
        }

        $settings = $this->clinicSettingsRepository->getOrCreateDefault();
        $rows = $this->weeklyScheduleRepository->findByDentist($dentist);

        return $this->json($this->scheduleMapper->toWeeklyResponse($dentist, $settings, $rows));
    }

    private function resolveDentistForCurrentUser(): ?Dentist
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return null;
        }

        $userId = $user->getId();
        if ($userId === null) {
            return null;
        }

        $dbUser = $this->userRepository->findForJwtPayload($userId);
        if ($dbUser === null) {
            return null;
        }

        $dentist = $dbUser->getDentist();
        if ($dentist !== null) {
            return $dentist;
        }

        $email = $dbUser->getEmail();
        if (is_string($email) && $email !== '') {
            return $this->dentistRepository->findOneByEmailIgnoreCase($email);
        }

        return null;
    }

    /**
     * MapRequestPayload a veces deja `days` como arrays; leemos el JSON crudo para no perder `shift`.
     *
     * @param WeeklyScheduleDayInputDTO[]|array<int, mixed> $mappedDays
     *
     * @return WeeklyScheduleDayInputDTO[]
     */
    private function resolveDaysFromRequest(Request $request, array $mappedDays): array
    {
        $raw = json_decode($request->getContent(), true);
        if (!\is_array($raw) || !\is_array($raw['days'] ?? null)) {
            return $this->coerceDayInputs($mappedDays);
        }

        $resolved = [];
        foreach ($raw['days'] as $item) {
            if (!\is_array($item)) {
                continue;
            }
            $day = new WeeklyScheduleDayInputDTO();
            $day->weekday = (int) ($item['weekday'] ?? 0);
            $day->shift = (string) ($item['shift'] ?? 'off');
            $resolved[] = $day;
        }

        return $resolved !== [] ? $resolved : $this->coerceDayInputs($mappedDays);
    }

    /**
     * @param WeeklyScheduleDayInputDTO[]|array<int, mixed> $items
     *
     * @return WeeklyScheduleDayInputDTO[]
     */
    private function coerceDayInputs(array $items): array
    {
        $resolved = [];
        foreach ($items as $item) {
            if ($item instanceof WeeklyScheduleDayInputDTO) {
                $resolved[] = $item;
                continue;
            }
            if (!\is_array($item)) {
                continue;
            }
            $day = new WeeklyScheduleDayInputDTO();
            $day->weekday = (int) ($item['weekday'] ?? 0);
            $day->shift = (string) ($item['shift'] ?? 'off');
            $resolved[] = $day;
        }

        return $resolved;
    }

    private function normalizeTime(string $raw, string $field): string
    {
        $value = trim($raw);
        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return substr($value, 0, 5);
        }
        throw new BadRequestHttpException(sprintf('Invalid time for %s.', $field));
    }
}
