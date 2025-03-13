<?php
namespace App\Controller;

use App\Entity\BattleLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/member/log', name: 'app_battle_log')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'empire du joueur actuel (vous devrez adapter cette partie en fonction de la manière dont vous gérez l'authentification de l'utilisateur).
        $currentEmpire = $this->getUser()->getSelectedEmpire(); // Exemple supposé de la méthode pour obtenir l'empire actuel de l'utilisateur.

        // Utiliser une requête DQL (Doctrine Query Language) pour sélectionner les logs en fonction de l'empire du joueur et les trier par ID dans l'ordre décroissant (du plus récent au plus ancien).
        $battleLogs = $entityManager->getRepository(BattleLog::class)
            ->createQueryBuilder('bl')
            ->where('bl.attacker = :empire OR bl.defender = :empire')
            ->setParameter('empire', $currentEmpire)
            ->orderBy('bl.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('log/index.html.twig', [
            'controller_name' => 'LogController',
            'battleLogs' => $battleLogs,
        ]);
    }

    #[Route('/member/log/{id}', name: 'app_battle_log_viewer')]
    public function viewer(EntityManagerInterface $entityManager, $id): Response
    {
        $battleLog = $entityManager->getRepository(BattleLog::class)->find($id);

        return $this->render('log/viewer.html.twig', [
            'controller_name' => 'LogController',
            'log' => $battleLog,
        ]);
    }
}

