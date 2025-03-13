<?php
namespace App\Service;

use App\Entity\Action;
use App\Service\BattleManager;
use Doctrine\ORM\EntityManagerInterface;

class ActionScheduler
{
    private EntityManagerInterface $entityManager;
    private BattleManager $battleManager;

    public function __construct(EntityManagerInterface $entityManager, BattleManager $battleManager)
    {
        $this->entityManager = $entityManager;
        $this->battleManager = $battleManager;
    }

    public function executeScheduledActions(): void
    {
        $currentTime = new \DateTime();
        $actionRepository = $this->entityManager->getRepository(Action::class);
    
        // Récupérer les actions prêtes à être exécutées ou en retour
        $actions = $actionRepository->createQueryBuilder('a')
            ->where('a.endTime <= :now')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('now', $currentTime)
            ->setParameter('statuses', ['En attente', 'Retour'])
            ->getQuery()
            ->getResult();
    
        foreach ($actions as $action) {
            if ($action->getStatus() === 'En attente') {
                $this->executeAction($action);
            } elseif ($action->getStatus() === 'Retour') {
                $this->finalizeReturn($action);
            }
        }
    }    

    private function executeAction(Action $action): void
    {
        $attackerEmpire = $action->getEmpire();
        $targetEmpire = $action->getTarget();
        $selectedUnits = $action->getArmy();
        $attackingArmy = $attackerEmpire->getUnit()->getArmy();
        $defendingArmy = $targetEmpire->getUnit()->getArmy() ?? [];

        // Appeler la logique de combat
        $this->battleManager->executeBattle(
            $selectedUnits,
            $attackingArmy,
            $defendingArmy,
            $attackerEmpire,
            $targetEmpire
        );

        // Mettre à jour le statut de l'action
        $action->setStatus('Terminé');
        
        $this->entityManager->persist($action);
        $this->entityManager->flush();
    }

    private function finalizeReturn(Action $action): void
    {
        $attackerEmpire = $action->getEmpire();
        $returnedUnits = $action->getArmy();
        $lootedResources = $action->getLootedResources();
    
        // Ajouter les ressources pillées à l'empire attaquant
        if (!empty($lootedResources)) {
            foreach ($lootedResources as $resourceInfoId => $quantity) {
                foreach ($attackerEmpire->getResources() as $existingResource) {
                    if ($existingResource->getInfo()->getId() === $resourceInfoId) {
                        // Augmenter la quantité de la ressource existante
                        $existingResource->setQuantity($existingResource->getQuantity() + $quantity);
                        break;
                    }
                }
            }
        }
    
        // Ajouter les unités retournées à l'armée de l'attaquant
        if (!empty($returnedUnits)) {
            $army = $attackerEmpire->getUnit()->getArmy();
            foreach ($returnedUnits as $unitId => $quantity) {
                if (isset($army[$unitId])) {
                    // Ajouter les unités retournées à celles existantes
                    $army[$unitId] += $quantity;
                } else {
                    // Ajouter une nouvelle entrée si l'unité n'existe pas encore
                    $army[$unitId] = $quantity;
                }
            }
            $attackerEmpire->getUnit()->setArmy($army);
        }
    
        // Marquer l'action comme terminée
        $action->setStatus('Terminé');
    
        // Persister les modifications
        $this->entityManager->persist($attackerEmpire);
        $this->entityManager->persist($action);
        $this->entityManager->flush();
    }      
}
