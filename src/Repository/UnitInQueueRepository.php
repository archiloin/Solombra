<?php

namespace App\Repository;

use App\Entity\UnitInQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnitInQueue>
 *
 * @method UnitInQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnitInQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnitInQueue[]    findAll()
 * @method UnitInQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnitInQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnitInQueue::class);
    }

//    /**
//     * @return UnitInQueue[] Returns an array of UnitInQueue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UnitInQueue
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
