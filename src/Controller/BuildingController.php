<?php

namespace App\Controller;

use App\Entity\BuildingLevel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Admin\ListResource;

#[Route(path: '/member/building')]
class BuildingController extends AbstractController
{
    #[Route('/', name: 'app_building')]
    public function index(): Response
    {
        return $this->render('building/index.html.twig', [
            'controller_name' => 'BuildingController',
        ]);
    }

    #[Route('/frequency', name: 'app_frequency')]
    public function frequency(): Response
    {
        return $this->render('building/frequency.html.twig', [
            'controller_name' => 'BuildingController',
        ]);
    }

    #[Route('/upgrade/{id}', name: 'app_building_upgrade')]
    public function upgrade(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
        $rateUpgrade = 1.3;
    
        if (!$selectedEmpire) {
            throw $this->createNotFoundException('No empire selected.');
        }
    
        $buildingLevel = $entityManager->getRepository(BuildingLevel::class)->findOneBy([
            'empire' => $selectedEmpire,
            'building' => $id,
        ]);
    
        if (!$buildingLevel) {
            throw $this->createNotFoundException('Building level not found.');
        }
    
        $resourcesEmpire = $selectedEmpire->getResources();
        $resourcesMap = [];
        foreach ($resourcesEmpire as $resource) {
            $resourcesMap[$resource->getInfo()->getId()] = $resource;
        }

        $upgradeCost = $buildingLevel->getBuilding()->getUpgradeCost();
        $ressourcesManquantes = [];
        foreach ($upgradeCost as $resourceId => $cost) {
            $updatedCost = ceil($cost * pow($rateUpgrade, $buildingLevel->getLevel()));
            if (!isset($resourcesMap[$resourceId]) || $resourcesMap[$resourceId]->getQuantity() < $updatedCost) {
                $resourceName = $resourcesMap[$resourceId]->getName();
                $ressourcesManquantes[] = $resourceName;
            }
        }

        if (!empty($ressourcesManquantes)) {
            $messageErreur = 'Vous n\'avez pas suffisamment de ' . implode(', ', $ressourcesManquantes) . ' pour augmenter le niveau de ce bâtiment.';
            return $this->json(['success' => false, 'error' => $messageErreur]);
        }

        foreach ($upgradeCost as $resourceId => $cost) {
            $updatedCost = $cost * pow($rateUpgrade, $buildingLevel->getLevel());
            if (isset($resourcesMap[$resourceId])) {
                $resource = $resourcesMap[$resourceId];
                $resource->setQuantity($resource->getQuantity() - $updatedCost);
                $entityManager->persist($resource);
            }
        }

        $level = $buildingLevel->getLevel();
        $building = $buildingLevel->getBuilding();
        $upgradeTime = $building->getUpgradeTime();
        $timeMultiplier = $building->getTimeMultiplier();

        // Calculer la nouvelle date de fin d'amélioration
        $upgradeEndTime = new \DateTime();
        $upgradeTimeInSeconds = round($upgradeTime * ($timeMultiplier ** $level)); // Calculer le temps pour le prochain niveau
        $upgradeEndTime->add(new \DateInterval('PT' . $upgradeTimeInSeconds . 'S'));

        // Mettre à jour les propriétés du bâtiment
        $buildingLevel->setUpgradeStartTime(new \DateTime());
        $buildingLevel->setUpgradeEndTime($upgradeEndTime);
            
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'buildingId' => $building->getId(),
            'upgradeEndTime' => $upgradeEndTime,
        ]);
    }
}
