<?php

namespace App\Service;

use App\Entity\Action;
use App\Entity\Empire;
use App\Entity\Admin\Unit;
use App\Entity\Battlelog;
use App\Service\SpeedCalculator;
use Doctrine\ORM\EntityManagerInterface;

class BattleManager
{
    private EntityManagerInterface $entityManager;
    private SpeedCalculator $speedCalculator;

    public function __construct(EntityManagerInterface $entityManager, SpeedCalculator $speedCalculator)
    {
        $this->entityManager = $entityManager;
        $this->speedCalculator = $speedCalculator;
    }

    public function executeBattle(
        array $selectedUnits,
        array &$attackingArmy,
        array &$defendingArmy,
        Empire $attackerEmpire,
        Empire $targetEmpire
    ): void {

        // Calculer le temps de trajet
        $speedData = $this->speedCalculator->calculSpeed($selectedUnits);
        $travelTimeInSeconds = $speedData['travelTimeInSeconds'];
        $executeTime = $speedData['executeTime'];

        // Vérification des empires
        if (!$targetEmpire) {
            throw new \InvalidArgumentException('Empire défenseur non trouvé.');
        }

        // Vérification des unités attaquantes
        if (empty($selectedUnits)) {
            throw new \InvalidArgumentException('Aucune unité sélectionnée pour l\'attaque.');
        }

        // Variables pour les logs
        $logDetails = "Bataille entre " . $attackerEmpire->getName() . " et " . $targetEmpire->getName() . "\n";
        $logDetails .= "\nForces initiales de l'Attaquant :\n";
        foreach ($selectedUnits as $unitId => $quantity) {
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if ($unit) {
                $logDetails .= "- " . $unit->getName() . ": " . $quantity . "\n";
            }
        }

        $logDetails .= "\nForces initiales du Défenseur :\n";
        foreach ($defendingArmy as $unitId => $quantity) {
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if ($unit) {
                $logDetails .= "- " . $unit->getName() . ": " . $quantity . "\n";
            }
        }

        // Combat : Calcul des pertes et mise à jour des armées
        $logDetails .= "\nDétails du combat :\n";
        foreach ($selectedUnits as $unitId => $attackerQuantity) {
            $attackingUnit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if (!$attackingUnit) {
                continue;
            }

            $attackerDamage = $attackingUnit->getAttack() * $attackerQuantity;

            foreach ($defendingArmy as $defenderId => $defenderQuantity) {
                $defendingUnit = $this->entityManager->getRepository(Unit::class)->find($defenderId);
                if (!$defendingUnit || $defenderQuantity <= 0) {
                    continue;
                }

                $defensePower = ($defendingUnit->getDefence() + $defendingUnit->getShield()) * $defenderQuantity;

                if ($attackerDamage >= $defensePower) {
                    $attackerDamage -= $defensePower;
                    $logDetails .= "- " . $attackingUnit->getName() . " détruit toutes les unités " . $defendingUnit->getName() . " (" . $defenderQuantity . " unités)\n";
                    $defendingArmy[$defenderId] = 0;
                } else {
                    $unitsLost = intdiv($attackerDamage, ($defendingUnit->getDefence() + $defendingUnit->getShield()));
                    $logDetails .= "- " . $attackingUnit->getName() . " détruit " . $unitsLost . " unités de " . $defendingUnit->getName() . "\n";
                    $defendingArmy[$defenderId] = max(0, $defenderQuantity - $unitsLost);
                    $attackerDamage = 0;
                }

                if ($attackerDamage <= 0) {
                    break;
                }
            }
        }

        // Supprimer les unités défensives détruites
        $defendingArmy = array_filter($defendingArmy, fn($quantity) => $quantity > 0);

        // Pillage des ressources uniquement si l'armée défensive est détruite
        $lootedResources = [];
        if (empty($defendingArmy)) {
            $logDetails .= "\nL'armée défensive a été détruite. Début du pillage des ressources...\n";
            $logDetails .= "\n+3 Exp au héro.\n";
            $hero = $attackerEmpire->getHero();
            $xp = $hero->getXp();
            $hero->setXp($xp + 3);
            $totalStorageCapacity = 0;
        
            foreach ($selectedUnits as $unitId => $quantity) {
                $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
                if ($unit) {
                    $totalStorageCapacity += $unit->getStockage() * $quantity;
                }
            }
        
            $resources = $targetEmpire->getResources();
            foreach ($resources as $resource) {
                $available = $resource->getQuantity();
                $toLoot = min($available, $totalStorageCapacity);
                $lootedResources[$resource->getInfo()->getId()] = $toLoot;
        
                $resource->setQuantity($available - $toLoot);
                $logDetails .= "- " . $resource->getName() . ": " . $toLoot . " pillé\n";
                $totalStorageCapacity -= $toLoot;
        
                if ($totalStorageCapacity <= 0) {
                    break;
                }
            }

            $currentTime = new \DateTime();
            $returnTime = (clone $currentTime)->modify("+{$travelTimeInSeconds} seconds");
        
            // Enregistrer les unités et les ressources pillées pour un retour différé
            $action = new Action();
            $action->setName('Retour des unités');
            $action->setStartTime($currentTime);
            $action->setEndTime($returnTime);
            $action->setStatus('Retour');
            $action->setEmpire($attackerEmpire);
            $action->setTarget($targetEmpire);
            $action->setLootedResources($lootedResources); // Correctement défini maintenant
            $action->setArmy($selectedUnits); // Ajoutez une colonne dans l'entité `Action` pour stocker les unités
            $this->entityManager->persist($action);            
        } else {
            $logDetails .= "\nL'armée défensive n'a pas été vaincue. Aucun pillage n'a eu lieu.\n";
            $logDetails .= "\n+1 Exp au héro.\n";
            $hero = $attackerEmpire->getHero();
            $xp = $hero->getXp();
            $hero->setXp($xp + 1);
        }

        // Mise à jour des entités
        $targetEmpire->getUnit()->setArmy($defendingArmy);

        // Forces finales pour le log
        $logDetails .= "\nForces finales de l'Attaquant :\n";
        foreach ($attackingArmy as $unitId => $quantity) {
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if ($unit) {
                $logDetails .= "- " . $unit->getName() . ": " . $quantity . "\n";
            }
        }

        $logDetails .= "\nForces finales du Défenseur :\n";
        foreach ($defendingArmy as $unitId => $quantity) {
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if ($unit) {
                $logDetails .= "- " . $unit->getName() . ": " . $quantity . "\n";
            }
        }

        // Création et persistance du log de bataille
        $battleLog = new Battlelog();
        $battleLog->setAttacker($attackerEmpire);
        $battleLog->setDefender($targetEmpire);
        $battleLog->setDetails($logDetails);
        $battleLog->setBattleTime(new \DateTime());
        $this->entityManager->persist($battleLog);

        // Persistance des données
        $this->entityManager->persist($targetEmpire);
        $this->entityManager->flush();
    }
}
