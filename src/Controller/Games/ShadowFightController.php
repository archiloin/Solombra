<?php

namespace App\Controller\Games;

use App\Entity\Games\ShadowFight;
use App\Entity\Games\ShadowFightConfig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShadowFightController extends AbstractController
{
    #[Route('/games/shadow-fight', name: 'app_shadow_fight')]
    public function shadowFight(EntityManagerInterface $entityManager): Response
    {
        $empire = $this->getUser()->getSelectedEmpire();
        $name = $empire->getHero()->getName();
        $health = $empire->getHero()->getHealth();
        $xp = $empire->getHero()->getXp();

        if (!$empire->getShadowFight()) {
            $shadowFight = new ShadowFight();
            $shadowFight->setEmpire($empire);
            $shadowFightConfig = $entityManager->getRepository(ShadowFightConfig::class)->find(1);
            $shadowFight->setConfig($shadowFightConfig);
            $shadowFightConfig = $shadowFight->getConfig();

            $entityManager->persist($shadowFight);
            $entityManager->persist($shadowFightConfig);
            $entityManager->flush();
        }
        else {
            $shadowFightConfig = $empire->getShadowFight()->getConfig();
        }

        $shadowName = $shadowFightConfig->getName();
        $shadowHealth = $shadowFightConfig->getHealth();
        $shadowXp = $shadowFightConfig->getXp();

        // Données initiales pour le jeu
        $data = [
            'player' => [
                'name' => $name,
                'health' => $health,
                'xp' => $xp,
                'mana' => 50,
                'skills' => ['Coup Furtif', 'Attaque Sombre', 'Bouclier de Nuit'],
            ],
            'enemy' => [
                'name' => $shadowName,
                'health' => $shadowHealth,
                'xp' => $shadowXp,
                'mana' => 30,
                'skills' => ['Frappe Obscure', 'Explosion Ténébreuse'],
            ],
        ];
        
        return $this->render('games/shadow_fight.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/api/shadow-fight/update-health', name: 'shadow_fight_update_health', methods: ['POST'])]
    public function updateHeroHealthAndShadowFightConfig(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $empire = $user->getSelectedEmpire();
    
        if (!$empire) {
            return new JsonResponse(['error' => 'Empire introuvable'], 400);
        }
    
        $data = json_decode($request->getContent(), true);
        if (!isset($data['heroHealth']) || !isset($data['enemyHealth']) || !isset($data['enemyXp'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }
    
        $hero = $empire->getHero();
        $hero->setHealth($data['heroHealth']);
    
        if ($data['enemyHealth'] <= 0) {
            // Ajouter l'XP au héros
            $currentXp = $hero->getXp() ?? 0; // Si l'XP est null, initialisez-le à 0
            $hero->setXp($currentXp + $data['enemyXp']);
    
            // Passer au niveau suivant de ShadowFightConfig
            $currentConfig = $empire->getShadowFight()->getConfig();
            $nextConfig = $entityManager->getRepository(ShadowFightConfig::class)->find($currentConfig->getId() + 1);
    
            if ($nextConfig) {
                $shadowFight = $empire->getShadowFight();
                $shadowFight->setConfig($nextConfig);
                $entityManager->persist($shadowFight);
            } else {
                return new JsonResponse(['message' => 'Aucun niveau suivant disponible pour le combat des ombres.']);
            }
        }
    
        $entityManager->persist($hero);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Santé et XP mises à jour avec succès.']);
    }
}
