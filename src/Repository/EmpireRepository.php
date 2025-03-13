<?php

namespace App\Repository;

use App\Entity\Empire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Empire>
 *
 * @method Empire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Empire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Empire[]    findAll()
 * @method Empire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Empire::class);
    }

//    /**
//     * @return Empire[] Returns an array of Empire objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Empire
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
