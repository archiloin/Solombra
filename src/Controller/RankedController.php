<?php

namespace App\Controller;

use App\Entity\Games\ShadowFightConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RankedController extends AbstractController
{
    #[Route('/ranked', name: 'app_ranked')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Utilisation du QueryBuilder pour trier et limiter les résultats
        $rankings = $entityManager->createQueryBuilder()
        ->select('sf, s')
        ->from(ShadowFightConfig::class, 'sf')
        ->leftJoin('sf.shadowFights', 's') // Jointure avec ShadowFight
        ->orderBy('sf.lvl', 'DESC')
        ->addOrderBy('sf.xp', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();    

        return $this->render('ranked/index.html.twig', [
            'controller_name' => 'RankedController',
            'rankings' => $rankings,
        ]);
    }
}
