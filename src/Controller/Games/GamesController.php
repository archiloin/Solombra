<?php

namespace App\Controller\Games;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class GamesController extends AbstractController
{
    #[Route('/member/games', name: 'app_games')]
    public function index(): Response
    {
        // Liste des jeux disponibles dans la salle de jeux
        $games = [
            [
                'name' => 'Puzzle Mystique',
                'description' => 'Résolvez des puzzles pour débloquer des images.',
                'route' => 'app_puzzle_mystique', // Route vers le jeu
                'image' => '/images/games/puzzle.jpg',
            ],
            [
                'name' => 'Combat des Ombres',
                'description' => 'Affrontez des adversaires redoutables dans l’arène.',
                'route' => 'app_shadow_fight',
                'image' => '/images/games/fight.jpg',
            ],
            [
                'name' => 'Aventure Runique',
                'description' => 'Résolvez des tuiles pour débloquer des images.',
                'route' => 'app_tile_mystique',
                'image' => '/images/games/adventure.jpg',
            ],
        ];

        return $this->render('games/index.html.twig', [
            'games' => $games,
        ]);
    }
}
