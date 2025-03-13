<?php

namespace App\Repository\Admin;

use App\Entity\Admin\ListHero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListHero>
 *
 * @method ListHero|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListHero|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListHero[]    findAll()
 * @method ListHero[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListHeroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListHero::class);
    }

//    /**
//     * @return ListHero[] Returns an array of ListHero objects
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

//    public function findOneBySomeField($value): ?ListHero
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
