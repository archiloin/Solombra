<?php

namespace App\Controller;

use App\Entity\Map;
use App\Entity\Empire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    #[Route(path: '/map/{dimension}', name: 'app_map')]
    public function index(EntityManagerInterface $entityManager, $dimension): Response
    {
        // Validation explicite du type de $dimension
        if (!ctype_digit((string)$dimension)) {
            $this->addFlash('error', 'La dimension doit être un entier valide petit malin :)');

            return $this->redirectToRoute('app_map', ['dimension'=> 1] );
        }
        
        if ($dimension >1000000000) {

            return $this->redirectToRoute('app_map', ['dimension'=> 1] );
        }

        $maps = $entityManager->getRepository(Map::class)->findAll();
        $zonesWithOwners = [];
    
        foreach ($maps as $map) {
            $owner = $entityManager->getRepository(Empire::class)->findOneBy([
                'zone' => $map,
                'dimension' => $dimension
            ]);
        
            if ($owner) {
                // Enregistrez à la fois le nom d'utilisateur et l'ID de l'empire
                $zonesWithOwners[$map->getId()] = [
                    'username' => $owner->getUser()->getUsername(),
                    'empireId' => $owner->getId()
                ];
            } else {
                $zonesWithOwners[$map->getId()] = ['username' => 'Aucun propriétaire', 'empireId' => null];
            }
        }        
    
        return $this->render('map.html.twig', [
            'maps' => $maps,
            'zonesWithOwners' => $zonesWithOwners,
            'dimension' => $dimension,
        ]);
    }
}
