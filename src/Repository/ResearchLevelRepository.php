<?php

namespace App\Repository;

use App\Entity\ResearchLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResearchLevel>
 *
 * @method ResearchLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResearchLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResearchLevel[]    findAll()
 * @method ResearchLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResearchLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResearchLevel::class);
    }

//    /**
//     * @return ResearchLevel[] Returns an array of ResearchLevel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ResearchLevel
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
