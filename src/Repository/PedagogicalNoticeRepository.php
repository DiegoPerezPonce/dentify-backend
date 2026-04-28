<?php

namespace App\Repository;

use App\Entity\PedagogicalNotice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PedagogicalNotice>
 */
class PedagogicalNoticeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PedagogicalNotice::class);
    }

    /**
     * Notices visible to students / clinical users (FE #21 notification hub).
     *
     * @return array{0: PedagogicalNotice[], 1: int}
     */
    public function findPublishedForReaders(?int $offset, ?int $limit): array
    {
        $now = new \DateTimeImmutable();

        $countQb = $this->createQueryBuilder('n');
        $this->applyReaderVisibility($countQb, $now);
        $countQb->select('COUNT(n.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('n');
        $this->applyReaderVisibility($qb, $now);
        $qb->orderBy('n.publishedAt', 'DESC');

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        /** @var PedagogicalNotice[] $items */
        $items = $qb->getQuery()->getResult();

        return [$items, $total];
    }

    /**
     * @param array{search?: string, active?: bool|null} $filters
     *
     * @return array{0: PedagogicalNotice[], 1: int}
     */
    public function findAdminList(array $filters, ?int $offset, ?int $limit): array
    {
        $countQb = $this->createQueryBuilder('n');
        $this->applyAdminFilters($countQb, $filters);
        $countQb->select('COUNT(n.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('n');
        $this->applyAdminFilters($qb, $filters);
        $qb->orderBy('n.publishedAt', 'DESC');

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        /** @var PedagogicalNotice[] $items */
        $items = $qb->getQuery()->getResult();

        return [$items, $total];
    }

    private function applyReaderVisibility(QueryBuilder $qb, \DateTimeImmutable $now): void
    {
        $qb->where('n.active = :active')
            ->andWhere('n.publishedAt <= :now')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt >= :now')
            ->setParameter('active', true)
            ->setParameter('now', $now);
    }

    /**
     * @param array{search?: string, active?: bool|null} $filters
     */
    private function applyAdminFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(n.title) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($filters['search']) . '%');
        }
        if (\array_key_exists('active', $filters) && $filters['active'] !== null) {
            $qb->andWhere('n.active = :activeFilter')
                ->setParameter('activeFilter', $filters['active']);
        }
    }
}
