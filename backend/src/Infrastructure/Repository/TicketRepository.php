<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Enum\TicketPriority;
use App\Domain\Enum\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function save(Ticket $ticket): void
    {
        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
    }

    public function remove(Ticket $ticket): void
    {
        $this->getEntityManager()->remove($ticket);
        $this->getEntityManager()->flush();
    }

    public function findByUser(User $user, ?TicketStatus $status = null, ?TicketPriority $priority = null, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.createdBy = :user')
            ->setParameter('user', $user);

        if ($status) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        if ($priority) {
            $qb->andWhere('t.priority = :priority')
               ->setParameter('priority', $priority);
        }

        $qb->orderBy('t.createdAt', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countByUser(User $user, ?TicketStatus $status = null, ?TicketPriority $priority = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.createdBy = :user')
            ->setParameter('user', $user);

        if ($status) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        if ($priority) {
            $qb->andWhere('t.priority = :priority')
               ->setParameter('priority', $priority);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
