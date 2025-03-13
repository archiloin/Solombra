<?php

namespace App\Repository;

use App\Entity\Action;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Action::class);
    }

    /**
     * Retrouver toutes les actions dont le temps de trajet est écoulé.
     *
     * @param \DateTime $currentTime Le moment actuel
     * @return Action[] Renvoie un tableau d'objets Action
     */
    public function findActionsWithElapsedTravelTime(\DateTime $currentTime)
    {
        return $this->createQueryBuilder('a')
            ->where('a.endTime <= :currentTime')
            ->setParameter('currentTime', $currentTime)
            ->getQuery()
            ->getResult();
    }
}
