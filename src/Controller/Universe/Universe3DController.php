<?php

namespace App\Controller\Universe;

use App\Entity\Universe\Messages;
use App\Entity\Universe\Players;
use App\Entity\Universe\Rooms;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/member/universe/3d')]
class Universe3DController extends AbstractController
{
    #[Route('/', name: 'app_universe_3d')]
    public function index(EntityManagerInterface $entityManager): Response
    {  
        return $this->render('universe/3d/index.html.twig', [
            'controller_name' => 'UniverseController',
        ]);
    }

    #[Route('/character', name: 'app_universe_3d_character')]
    public function character(EntityManagerInterface $entityManager): Response
    {  
        return $this->render('universe/3d/character.html.twig', [
            'controller_name' => 'UniverseController',
        ]);
    }

    #[Route('/room/{roomId}', name: 'app_universe_3d_room', methods: ['GET'])]
    public function getRoomData(EntityManagerInterface $entityManager, int $roomId): JsonResponse
    {
        // Récupérer les joueurs dans la pièce
        $players = $entityManager->getRepository(Players::class)->findBy(['room_id' => $roomId]);
        $playerData = array_map(function ($player) {
            return [
                'id' => $player->getId(),
                'name' => $player->getName(),
                'x' => $player->getX(),
                'y' => $player->getY(),
            ];
        }, $players);

        // Récupérer les messages dans la pièce (limité à 20)
        $messages = $entityManager->getRepository(Messages::class)
            ->findBy(['room_id' => $roomId], ['sent_at' => 'DESC'], 20);
        $messageData = array_map(function ($message) use ($entityManager) {
            // Récupérer le nom du joueur à partir de l'ID
            $player = $entityManager->getRepository(Players::class)->find($message->getPlayerId());
            return [
                'player_id' => $message->getPlayerId(),
                'name' => $player ? $player->getName() : 'Unknown',
                'message' => $message->getMessage(),
                'sent_at' => $message->getSentAt()->format('Y-m-d H:i:s'),
            ];
        }, $messages);

        // Retourner les données formatées
        return new JsonResponse([
            'players' => $playerData,
            'messages' => array_reverse($messageData), // Inverser pour un ordre chronologique
        ]);
    }

    #[Route('/room/{roomId}/message', name: 'app_universe_3d_message', methods: ['POST'])]
    public function postMessage(EntityManagerInterface $entityManager, Request $request, int $roomId): JsonResponse
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }
    
        // Récupérer l'empire sélectionné par l'utilisateur
        $selectedEmpire = $user->getSelectedEmpire();
        if (!$selectedEmpire) {
            return new JsonResponse(['error' => 'Empire not selected'], 400);
        }
    
        $data = json_decode($request->getContent(), true);
    
        $playerId = $selectedEmpire->getId();
        $messageText = $data['message'] ?? null;
    
        // Vérifier les données envoyées
        if (!$playerId || !$messageText) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }
    
        // Créer et sauvegarder le message
        $message = new Messages();
        $message->setRoomId($roomId)
            ->setPlayerId($playerId)
            ->setMessage($messageText)
            ->setSentAt(new \DateTimeImmutable());
    
        $entityManager->persist($message);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true]);
    }    

    #[Route('/room/{roomId}/player', name: 'app_universe_3d_player', methods: ['POST'])]
    public function addOrUpdatePlayer(EntityManagerInterface $entityManager, Request $request, int $roomId): JsonResponse
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
        $data = json_decode($request->getContent(), true);

        $playerId = $selectedEmpire->getId();
        $x = $data['x'] ?? null;
        $y = $data['y'] ?? null;

        if (!$playerId || $x === null || $y === null) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $player = $entityManager->getRepository(Players::class)->findOneBy(['empire' => $selectedEmpire]);

        if (!$player) {
            $player = new Players();
            $player->setEmpire($selectedEmpire)
                ->setRoomId($roomId)
                ->setName($data['name'] ?? 'Player');
        }

        $player->setX($x)
            ->setY($y)
            ->setLastSeen(new \DateTime());

        $entityManager->persist($player);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
