<?php

namespace App\Controller\Games;

use App\Entity\Games\TileMystique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TileMystiqueController extends AbstractController
{
    #[Route('/member/tuile-mystique', name: 'app_tile_mystique')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $scores = $entityManager->getRepository(TileMystique::class)
            ->findBy([], ['score' => 'DESC'], 10); // Trier par score décroissant, limiter à 10 résultats

        $data = [
            'tiles' => ['A', 'B', 'C', 'D'], // Exemple de données de puzzle
        ];

        return $this->render('games/tile_mystique.html.twig', [
            'scores' => $scores,
            'data' => $data,
        ]);
    }

    #[Route('/api/tuile-mystique/update-score', name: 'tile_mystique_update_score', methods: ['POST'])]
    public function updateScore(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $playerName = $this->getUser()->getUsername();
        $score = $data['score'];
    
        $scoreEntry = new TileMystique();
        $scoreEntry->setPlayerName($playerName);
        $scoreEntry->setScore($score);
        $scoreEntry->setCreatedAt(new \DateTimeImmutable());
    
        $entityManager->persist($scoreEntry);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Score enregistré avec succès.']);
    }
    
}
