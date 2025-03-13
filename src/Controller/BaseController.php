<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\BuildingLevel;
use App\Entity\Resource;
use App\Entity\Hero;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class BaseController extends AbstractController
{
    public function checkUpgradeStatus(BuildingLevel $buildingLevel, Hero $hero): bool
    {
        $currentTime = new \DateTime();
        $upgradeEndTime = $buildingLevel->getUpgradeEndTime();
    
        if ($upgradeEndTime && $upgradeEndTime <= $currentTime) {
            // Construction terminée, augmenter le niveau du bâtiment
            $addLevel = $buildingLevel->getLevel() + 1;
            $buildingLevel->setLevel($addLevel);
            $buildingLevel->setUpgradeStartTime(null);
            $buildingLevel->setUpgradeEndTime(null);
            $hero->addXp($addLevel);
    
            return true;
        }

        return false;
    }

    public function getBuildingsAndTimes(EntityManagerInterface $em, $selectedEmpire, $hero): array
    {
        $buildingLevels = $selectedEmpire->getBuildingLevels();
        $repository = $em->getRepository(Building::class);
        $buildings = $repository->findAll();
    
        $buildingTimes = [];
    
        foreach ($buildingLevels as $buildingLevel) {
            // Vérifie si la construction est terminée
            if ($this->checkUpgradeStatus($buildingLevel, $hero)) {
                $em->flush();
            }
    
            $level = $buildingLevel->getLevel();
            $building = $buildingLevel->getBuilding();
            $upgradeTime = $building->getUpgradeTime();
            $timeMultiplier = $building->getTimeMultiplier();
    
            $upgradeTimeInSeconds = round($upgradeTime * pow($timeMultiplier, $level));
            $days = floor($upgradeTimeInSeconds / (60 * 60 * 24));
            $hours = floor(($upgradeTimeInSeconds % (60 * 60 * 24)) / (60 * 60));
            $minutes = floor(($upgradeTimeInSeconds % (60 * 60)) / 60);
            $seconds = $upgradeTimeInSeconds % 60;
    
            $buildingTimes[$building->getId()] = [
                'days' => $days,
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds,
            ];
        }
    
        return [$buildings, $buildingTimes];
    }    

    #[Route('/update-resource-name/{id}', name: 'update_resource_name', methods: ['POST'])]
    public function updateResourceName(Request $request, EntityManagerInterface $em, $id, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Vérifiez le token CSRF
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('edit-resource', $csrfToken))) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $resource = $em->getRepository(Resource::class)->find($id);
        if (!$resource) {
            return new JsonResponse(['error' => 'Ressource non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $newName = trim($data['name']);
        if (empty($newName)) {
            $newName = 'à définir';
        }

        $resource->setName($newName);
        $em->flush();

        return new JsonResponse(['success' => 'Nom de la ressource mis à jour avec succès']);
    }

}