<?php
namespace App\Service;


use App\Repository\EmpireRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Resource;

class ResourceUpdaterService
{
    private $empireRepository;
    private $entityManager;

    public function __construct(
        EmpireRepository $empireRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->empireRepository = $empireRepository;
        $this->entityManager = $entityManager;
    }

    public function updateResources() {
        $empires = $this->empireRepository->findAll();

        foreach ($empires as $empire) {
            $buildingLevels = $empire->getBuildingLevels();

            foreach ($buildingLevels as $buildingLevel) {
                $building = $buildingLevel->getBuilding();
                $level = $buildingLevel->getLevel();
                $productionRate = $building->getProductionRate();
                $resourcePerLevel = $building->getResourcePerLevel();

                // Calculez la quantité de ressources produites
                $quantityProduced = $level * $resourcePerLevel * $productionRate;

                // Obtenez le type de ressource produit par le bâtiment
                $resourceType = $building->getResourceType();

                // Trouvez la ressource correspondante dans l'empire
                foreach ($empire->getResources() as $resource) {
                    if ($resource->getInfo()->getId() == $resourceType) {
                        // Mise à jour de la quantité
                        $currentQuantity = $resource->getQuantity();
                        $resource->setQuantity($currentQuantity + $quantityProduced);

                        // Persistez les changements
                        $this->entityManager->persist($resource);
                        break; // Arrêtez la boucle une fois la ressource trouvée et mise à jour
                    }
                }
            }
        }

        // Flush les changements à la fin
        $this->entityManager->flush();
    }
}
