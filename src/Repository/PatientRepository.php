<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * @return array{items: list<Patient>, total: int}
     */
    public function findPaginated(?string $searchTerm, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');

        if ($searchTerm !== null && $searchTerm !== '') {
            $term = trim($searchTerm);
            if ($term !== '') {
                $like = '%' . $term . '%';
                $digits = preg_replace('/\D+/', '', $term) ?? '';

                $clauses = [
                    'p.dni LIKE :term',
                    'p.nss LIKE :term',
                    'p.nombre LIKE :term',
                    'p.apellidos LIKE :term',
                    'p.email LIKE :term',
                    'CONCAT(p.nombre, \' \', p.apellidos) LIKE :term',
                    'p.telefono LIKE :term',
                    'p.historial_clinico LIKE :term',
                ];
                if ($digits !== '' && \strlen($digits) >= 3) {
                    $clauses[] = 'p.telefono LIKE :termDigits';
                    $qb->setParameter('termDigits', '%' . $digits . '%');
                }

                $qb->andWhere($qb->expr()->orX(...$clauses))
                    ->setParameter('term', $like);
            }
        }

        $first = max(0, ($page - 1) * $itemsPerPage);
        $query = $qb->setFirstResult($first)->setMaxResults($itemsPerPage)->getQuery();
        $paginator = new Paginator($query, false);

        return [
            'items' => iterator_to_array($paginator->getIterator()),
            'total' => $paginator->count(),
        ];
    }
}
