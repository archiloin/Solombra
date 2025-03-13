<?php
namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Unit;
use App\Entity\UnitInQueue;
use App\Entity\Resource;
use App\Entity\Admin\Unit as ListUnit;
use App\Entity\Empire;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(path: '/member/unit')]
class UnitController extends BaseController
{
    private function checkBuyingStatus(EntityManagerInterface $entityManager, UnitInQueue $unitInQueue, Unit $unit): bool
    {
        $currentTime = new \DateTime();
        $endTime = $unitInQueue->getEndTime();
    
        if ($endTime && $endTime <= $currentTime) {
            // Récupérer l'unité associée à UnitInQueue et le tableau Army actuel
            $unitList = $unitInQueue->getUnit();
            $army = $unit->getArmy();
    
            // Nom de l'unité stocké dans le tableau army
            $unitId = $unitList->getId();
            $quantity = $unitInQueue->getQuantity();
    
            // Mettre à jour le tableau Army avec la nouvelle unité
            if (isset($army[$unitId])) {
                $army[$unitId] += $quantity;
            } else {
                $army[$unitId] = $quantity;
            }
    
            // Sauvegarder le tableau Army mis à jour
            $unit->setArmy($army);
    
            // Supprimer UnitInQueue de la base de données
            $entityManager->remove($unitInQueue);
            $entityManager->persist($unit);
            $entityManager->flush();
    
            return true;
        }
    
        return false;
    }    
    
    #[Route('/{category}', name: 'app_unit')]
    public function index(string $category, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
        $hero = $selectedEmpire->getHero();
        
        // Appeler la fonction pour obtenir les bâtiments et les temps associés
        [$buildings, $buildingTimes] = $this->getBuildingsAndTimes($entityManager, $selectedEmpire, $hero);

        // Liste des propriétés valides de la classe Force
        $validProperties = ['weak', 'strong', 'electromagnetic', 'gravity'];

        // Vérification si le nom de la propriété est valide
        if (!in_array($category, $validProperties)) {
            throw new \InvalidArgumentException("Merci de ne plus changer l'url manuellement :)");
        }

        $units = $entityManager->getRepository(ListUnit::class)->findUnitsByForceProperty($category, true);
        // Supposons que vous ayez une méthode pour récupérer les UnitInQueues
        $unitsInQueue = $entityManager->getRepository(UnitInQueue::class)->findBy([
            'empire' => $selectedEmpire
        ]);

        // Vérifiez s'il existe déjà une unité pour l'empire sélectionné
        $existingUnit = $selectedEmpire->getUnit();

        // Si aucune unité n'existe, créez-en une
        if (!$existingUnit instanceof \App\Entity\Unit) {
            $newUnit = new Unit();
            $newUnit->setEmpire($selectedEmpire);
            $entityManager->persist($newUnit);
            $entityManager->flush();
        }

        foreach ($unitsInQueue as $queueUnit) {
            // Obtenez la première unité de la collection, si elle existe
            $unit = $selectedEmpire->getUnit();

            // Vérifiez si $unit est une instance de Unit
            if ($unit instanceof \App\Entity\Unit) {
                $this->checkBuyingStatus($entityManager, $queueUnit, $unit);
            }
        }
        $armyArray = $selectedEmpire->getUnit()->getArmy();
        return $this->render('unit/index.html.twig', [
            'units' => $units,
            'unitsInQueue' => $unitsInQueue,
            'selectedEmpire' => $selectedEmpire,
            'category' => $category,
            'armyArray' => $armyArray,
            'buildings' => $buildings,
            'buildingTimes' => $buildingTimes,
        ]);
    }

    #[Route('/{category}/buy/{id}', name: 'app_unit_buy')]
    public function buy(int $id, string $category, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
    
        if (!$selectedEmpire) {
            throw $this->createNotFoundException('No empire selected.');
        }
    
        $listUnit = $entityManager->getRepository(ListUnit::class)->find($id);
    
        if (!$listUnit) {
            throw $this->createNotFoundException('Unit not found.');
        }
    
        $resourcesMap = [];
        foreach ($selectedEmpire->getResources() as $resource) {
            $resourcesMap[$resource->getInfo()->getId()] = $resource->getQuantity();
        }

        // Récupérer la quantité à partir de la requête
        $quantity = $request->request->get('quantity', 1); // Valeur par défaut à 1
    
        $birthCost = $listUnit->getBirthCost();
        // Supposons que $birthCost est déjà un tableau avec les clés étant les IDs des ressources et les valeurs les coûts
        foreach ($birthCost as $resourceId => $cost) {
            $totalCost = $cost * $quantity; // Multiplier le coût par la quantité
            if (isset($resourcesMap[$resourceId]) && $resourcesMap[$resourceId] >= $totalCost) {
                // Mise à jour de la quantité disponible après achat
                $resourcesMap[$resourceId] -= $totalCost;
                
                // Mettre à jour l'entité Resource correspondante
                foreach ($selectedEmpire->getResources() as $resource) {
                    if ($resource->getInfo()->getId() == $resourceId) {
                        $resource->setQuantity($resourcesMap[$resourceId]);
                        $entityManager->persist($resource);
                        break; // Sortir dès que la ressource correspondante est mise à jour
                    }
                }
            } else {
                // Nom de la ressource pour l'affichage dans le message d'erreur
                $resourceName = $entityManager->getRepository(Resource::class)->findOneBy(['info' => $resourceId, 'empire' => $selectedEmpire])->getName();
                
                $this->addFlash('error', "Vous n'avez pas assez de $resourceName pour acheter cette quantité d'unités.");
                return $this->redirectToRoute('app_unit', [
                    'category' => $category,
                ]);
            }
        }

        // Trouvez la dernière unité en file d'attente pour cet empire
        $lastUnitInQueue = $entityManager->getRepository(UnitInQueue::class)->findOneBy(
            ['empire' => $selectedEmpire],
            ['endTime' => 'DESC']
        );
    
        // Définir le startTime pour la nouvelle unité
        $startTime = $lastUnitInQueue ? $lastUnitInQueue->getEndTime() : new \DateTime();
    
        $birthTime = $listUnit->getBirthTime();
        $endTime = clone $startTime;
        $totalBirthTime = $birthTime * $quantity;
        $endTime->add(new \DateInterval('PT' . $totalBirthTime . 'S'));
    
        $newUnit = new UnitInQueue();
        $newUnit->setEmpire($selectedEmpire);
        $newUnit->setQuantity($quantity);
        $newUnit->setUnit($listUnit);
        $newUnit->setStartTime($startTime);
        $newUnit->setEndTime($endTime);
    
        $entityManager->persist($newUnit);
        $entityManager->flush();
    
        // Rediriger vers la page des unités
        return $this->redirectToRoute('app_unit', [
            'category' => $category,
        ]);
    }
    
}
