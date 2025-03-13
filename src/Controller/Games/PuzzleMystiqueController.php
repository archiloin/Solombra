<?php

namespace App\Controller\Games;

use App\Entity\Games\PuzzleMystique\PuzzleProgress;
use App\Repository\Games\PuzzleProgressRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/member')]
class PuzzleMystiqueController extends AbstractController
{
    #[Route('/puzzle-mystique', name: 'app_puzzle_mystique')]
    public function index(
        PuzzleProgressRepository $puzzleProgressRepository
    ): Response {
        $user = $this->getUser();
    
        // Tailles fixes de puzzle
        $fixedSizes = [12, 25, 50, 100, 200];
        $puzzleData = [];
    
        // Initialiser avec des valeurs par défaut
        foreach ($fixedSizes as $size) {
            $puzzleData[$size] = [
                'completedPieces' => 0,
                'totalPieces' => $size,
                'completed' => false,
            ];
        }
    
        // Récupérer les progressions de puzzles de l'utilisateur
        $puzzleProgresses = $puzzleProgressRepository->findBy(['user' => $user]);
    
        // Mettre à jour les données avec les progressions existantes
        foreach ($puzzleProgresses as $progress) {
            $state = json_decode($progress->getState(), true) ?: [];
            $size = $progress->getSize();
            if (isset($puzzleData[$size])) {
                $puzzleData[$size] = [
                    'completedPieces' => $state['completedPieces'] ?? 0,
                    'totalPieces' => $size,
                    'completed' => $progress->isCompleted(),
                ];
            }
        }
    
        return $this->render('games/puzzle_mystique.html.twig', [
            'puzzleData' => $puzzleData,
            'fixedSizes' => $fixedSizes, // Ajout des tailles fixes
        ]);
    }    

    #[Route('/save-puzzle-progress', name: 'save_puzzle_progress', methods: ['POST'])]
    public function savePuzzleProgress(
        Request $request,
        EntityManagerInterface $entityManager,
        PuzzleProgressRepository $puzzleProgressRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
    
        $user = $this->getUser();
    
        $puzzleType = $data['puzzleType'] ?? null;
        $puzzleSize = $data['puzzleSize'] ?? null;
    
        if (!$puzzleType || !$puzzleSize) {
            return new JsonResponse(['error' => 'Missing puzzle type or size'], 400);
        }
    
        $state = $data['state'] ?? ['pieces' => []];
    
        $progress = $puzzleProgressRepository->findOneBy([
            'user' => $user,
            'type' => $puzzleType,
            'size' => $puzzleSize,
        ]);
    
        if (!$progress) {
            $progress = new PuzzleProgress();
            $progress->setUser($user);
            $progress->setType($puzzleType);
            $progress->setSize($puzzleSize);
        }
    
        $progress->setState(json_encode([
            'completedPieces' => 0,
            'pieces' => $state['pieces']
        ]));
        $progress->setCompleted(false);
        $progress->setUpdatedAt(new \DateTime());
    
        $entityManager->persist($progress);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'state' => $progress->getState()
        ]);
    }
    

    #[Route('/load-puzzle-progress', name: 'load_puzzle_progress', methods: ['GET'])]
    public function loadPuzzleProgress(
        Request $request,
        PuzzleProgressRepository $puzzleProgressRepository
    ): JsonResponse {
        $user = $this->getUser();
        $puzzleType = $request->query->get('type');
        $puzzleSize = $request->query->get('size');
    
        $progress = $puzzleProgressRepository->findOneBy([
            'user' => $user,
            'type' => $puzzleType,
            'size' => $puzzleSize,
        ]);
    
        if (!$progress) {
            // Puzzle non existant en BDD
            return new JsonResponse([
                'state' => null,
                'completed' => false,
            ]);
        }
    
        return new JsonResponse([
            'state' => json_decode($progress->getState(), true),
            'completed' => $progress->isCompleted(),
        ]);
    }
    

    #[Route('/mark-puzzle-complete', name: 'mark_puzzle_complete', methods: ['POST'])]
    public function markPuzzleComplete(
        Request $request,
        EntityManagerInterface $entityManager,
        PuzzleProgressRepository $puzzleProgressRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();
        $puzzleType = $data['puzzleType'];
        $puzzleSize = $data['puzzleSize'];

        $progress = $puzzleProgressRepository->findOneBy([
            'user' => $user,
            'type' => $puzzleType,
            'size' => $puzzleSize,
        ]);

        if (!$progress) {
            return new JsonResponse(['error' => 'Puzzle progress not found'], 404);
        }

        // Marquer le puzzle comme terminé
        $progress->setCompleted(true);
        $progress->setUpdatedAt(new \DateTime());

        $entityManager->persist($progress);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Puzzle marked as complete']);
    }
}
