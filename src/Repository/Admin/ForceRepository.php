<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Force;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Force>
 *
 * @method Force|null find($id, $lockMode = null, $lockVersion = null)
 * @method Force|null findOneBy(array $criteria, array $orderBy = null)
 * @method Force[]    findAll()
 * @method Force[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Force::class);
    }
}