<?php

namespace App\Controller;

use App\Dto\PedagogicalNoticeCreateDTO;
use App\Dto\PedagogicalNoticeUpdateDTO;
use App\Entity\PedagogicalNotice;
use App\Entity\User;
use App\Mapper\PedagogicalNoticeMapper;
use App\Repository\PedagogicalNoticeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pedagogical-notices')]
class PedagogicalNoticeController extends AbstractController
{
    public function __construct(
        private PedagogicalNoticeRepository $noticeRepository,
        private PedagogicalNoticeMapper $noticeMapper,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Published notices for dashboards / notification center (FE #21). ROLE_USER + ROLE_ADMIN.
     */
    #[Route('', name: 'app_pedagogical_notice_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listPublished(Request $request): JsonResponse
    {
        $pagination = $this->resolveListPagination($request);
        if ($pagination instanceof JsonResponse) {
            return $pagination;
        }
        [$offset, $limit] = $pagination;

        [$items, $total] = $this->noticeRepository->findPublishedForReaders($offset, $limit);

        $response = $this->json(array_map(
            fn (PedagogicalNotice $n) => $this->noticeMapper->toResponseDto($n),
            $items
        ));
        $response->headers->set('X-Total-Count', (string) $total);

        return $response;
    }

    /**
     * Full list for admin UI (includes inactive / scheduled / expired). Must be registered before /{id}.
     */
    #[Route('/manage', name: 'app_pedagogical_notice_manage_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function manageList(Request $request): JsonResponse
    {
        $filters = [];

        $search = $request->query->get('search');
        if (is_string($search) && trim($search) !== '') {
            $filters['search'] = trim($search);
        }

        $activeRaw = $request->query->get('active');
        if ($activeRaw !== null && $activeRaw !== '') {
            $parsed = filter_var($activeRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($parsed === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid active. Use true or false.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters['active'] = $parsed;
        } else {
            $filters['active'] = null;
        }

        $pagination = $this->resolveListPagination($request);
        if ($pagination instanceof JsonResponse) {
            return $pagination;
        }
        [$offset, $limit] = $pagination;

        [$items, $total] = $this->noticeRepository->findAdminList($filters, $offset, $limit);

        $response = $this->json(array_map(
            fn (PedagogicalNotice $n) => $this->noticeMapper->toResponseDto($n),
            $items
        ));
        $response->headers->set('X-Total-Count', (string) $total);

        return $response;
    }

    #[Route('/{id}', name: 'app_pedagogical_notice_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function get(PedagogicalNotice $notice): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isVisibleToReaders($notice)) {
            throw $this->createNotFoundException();
        }

        return $this->json($this->noticeMapper->toResponseDto($notice));
    }

    #[Route('', name: 'app_pedagogical_notice_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(#[MapRequestPayload] PedagogicalNoticeCreateDTO $dto): JsonResponse
    {
        try {
            $notice = $this->noticeMapper->toEntity($dto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $notice->setCreatedBy($user);
        }

        $this->entityManager->persist($notice);
        $this->entityManager->flush();

        return $this->json(
            $this->noticeMapper->toResponseDto($notice),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_pedagogical_notice_update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(PedagogicalNotice $notice, #[MapRequestPayload] PedagogicalNoticeUpdateDTO $dto): JsonResponse
    {
        try {
            $this->noticeMapper->updateEntity($notice, $dto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->flush();

        return $this->json($this->noticeMapper->toResponseDto($notice));
    }

    #[Route('/{id}', name: 'app_pedagogical_notice_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(PedagogicalNotice $notice): JsonResponse
    {
        $this->entityManager->remove($notice);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function isVisibleToReaders(PedagogicalNotice $notice): bool
    {
        if (!$notice->isActive()) {
            return false;
        }
        $now = new \DateTimeImmutable();
        if ($notice->getPublishedAt() > $now) {
            return false;
        }
        $exp = $notice->getExpiresAt();
        if ($exp !== null && $exp < $now) {
            return false;
        }

        return true;
    }

    /**
     * @return JsonResponse|array{0: int|null, 1: int|null}
     */
    private function resolveListPagination(Request $request): JsonResponse|array
    {
        $pageRaw = $request->query->get('page');
        $itemsRaw = $request->query->get('itemsPerPage');

        if (
            ($pageRaw === null || $pageRaw === '')
            && ($itemsRaw === null || $itemsRaw === '')
        ) {
            return [null, null];
        }

        if ($pageRaw !== null && $pageRaw !== '' && (!ctype_digit((string) $pageRaw) || (int) $pageRaw < 1)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid page.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($itemsRaw !== null && $itemsRaw !== '' && !ctype_digit((string) $itemsRaw)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid itemsPerPage.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $page = ($pageRaw === null || $pageRaw === '') ? 1 : (int) $pageRaw;
        $itemsPerPage = ($itemsRaw === null || $itemsRaw === '') ? 30 : max(1, min(100, (int) $itemsRaw));

        return [($page - 1) * $itemsPerPage, $itemsPerPage];
    }
}
