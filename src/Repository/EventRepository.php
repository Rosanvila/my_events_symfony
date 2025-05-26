<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function search(array $data): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.category', 'c');

        if (!empty($data['name'])) {
            $qb->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $data['name'] . '%');
        }

        if (!empty($data['location'])) {
            $qb->andWhere('e.location LIKE :location')
                ->setParameter('location', '%' . $data['location'] . '%');
        }

        if (!empty($data['startDate'])) {
            $qb->andWhere('e.startDate >= :startDate')
                ->setParameter('startDate', $data['startDate']);
        }

        if (!empty($data['category'])) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $data['category']->getId());
        }

        return $qb->getQuery()->getResult();
    }

    public function findUpcomingEvents(int $limit = 6): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.startDate > :now')
            ->setParameter('now', new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->orderBy('e.startDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
