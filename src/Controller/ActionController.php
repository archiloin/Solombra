<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Admin\Unit;
use App\Entity\Empire;
use App\Entity\Resource;
use App\Entity\Battlelog;
use App\Form\UnitSelectionType;
use App\Service\SpeedCalculator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class ActionController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager, SpeedCalculator $speedCalculator)
    {
        $this->entityManager = $entityManager;
        $this->speedCalculator = $speedCalculator;
    }

    private function getFullUnitNames($availableUnits): array
    {
        $fullUnitNames = [];
        foreach ($availableUnits as $unitId => $quantity) {
            $unitName = // Méthode pour obtenir le nom de l'unité
            $fullUnitNames[$unitId] = $unitName . ' (Quantité disponible : ' . $quantity . ')';
        }
        return $fullUnitNames;
    }
    
    #[Route('/prepare-action/{targetEmpireId}/{dimension}/{zone}', name: 'app_prepare_action')]
    public function prepareAction(int $targetEmpireId, int $dimension, int $zone, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $attackerEmpire = $user->getSelectedEmpire();

        // Obtenir l'empire défenseur
        $targetEmpire = $entityManager->getRepository(Empire::class)->find($targetEmpireId);
        if (!$targetEmpire) {
            throw $this->createNotFoundException('Empire défenseur non trouvé.');
        }

        // Obtenir les unités disponibles pour l'attaque
        $availableUnits = $attackerEmpire->getUnit()->getArmy() ?? []; // Assurez-vous que c'est un tableau

        $form = $this->createForm(UnitSelectionType::class, null, [
            'available_units' => $availableUnits,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $selectedUnits = [];
        
            // Récupérer les données de selectedUnits depuis le formulaire
            foreach ($availableUnits as $unitId => $quantity) {
                $fieldId = 'unit_quantity_' . $unitId;
                $selectedQuantity = $formData[$fieldId];
        
                // Vérifier si le joueur a saisi un nombre négatif
                if ($selectedQuantity < 0) {
                    $this->addFlash('error', 'Vous ne pouvez pas envoyer un nombre négatif d\'unités.');
                    return $this->redirectToRoute('app_prepare_action', [
                        'targetEmpireId' => $targetEmpireId,
                        'dimension' => $dimension,
                        'zone' => $zone,
                    ]);
                }
            
                // Vérifier si le joueur n'envoie pas plus d'unités qu'il n'en possède
                if ($selectedQuantity > $quantity) {
                    $this->addFlash('error', 'Vous ne pouvez pas envoyer plus d\'unités que vous n\'en avez.');
                    return $this->redirectToRoute('app_prepare_action', [
                        'targetEmpireId' => $targetEmpireId,
                        'dimension' => $dimension,
                        'zone' => $zone,
                    ]);
                }
        
                $selectedUnits[$unitId] = $selectedQuantity;
            }
            
            // Stocker les données de selectedUnits dans la session
            $session = $request->getSession();
            $session->set('targetEmpireId', $targetEmpireId);
            $session->set('selectedUnits', $selectedUnits); // Stocker les données dans la session
    
            // Redirection vers la confirmation d'attaque
            return $this->redirectToRoute('app_confirm_action', [
                'targetEmpireId' => $targetEmpireId,
                'dimension' => $dimension,
                'zone' => $zone,
            ]);
        }

        // Passer les données à la vue de sélection des unités
        return $this->render('action/prepare.html.twig', [
            'availableUnits' => $availableUnits,
            'targetEmpire' => $targetEmpire,
            'dimension' => $dimension,
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirm-action/{targetEmpireId}/{dimension}/{zone}', name: 'app_confirm_action')]
    public function confirmAction(int $targetEmpireId, int $dimension, Request $request, EntityManagerInterface $entityManager, $zone): Response
    {
        $user = $this->getUser();
        $userEmpire = $user->getSelectedEmpire();
        // Récupérer l'empire défenseur à partir de son ID
        $targetEmpire = $entityManager->getRepository(Empire::class)->find($targetEmpireId);
    
        // Récupérer les données de selectedUnits depuis la session
        $session = $request->getSession();
        $selectedUnits = $session->get('selectedUnits'); // Récupérer les données de la session
    
        // Créez une instance de votre formulaire
        $form = $this->createForm(UnitSelectionType::class, null, [
            'available_units' => $selectedUnits,
        ]);

        // Utilisez le formulaire pour obtenir les noms liés aux ID
        $unitNames = [];
        foreach ($selectedUnits as $unitId => $quantity) {
            $unitNames[$unitId] = $form->get('unit_quantity_' . $unitId)->getConfig()->getOption('label');
        }

        try {
            // Calculer le temps de trajet
            $speedData = $this->speedCalculator->calculSpeed($selectedUnits);
            $travelTimeInSeconds = $speedData['travelTimeInSeconds'];
            $executeTime = $speedData['executeTime'];    
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de calcul de la vitesse : ' . $e->getMessage());
            return $this->redirectToRoute('app_prepare_action', [
                'targetEmpireId' => $targetEmpireId,
                'dimension' => $dimension,
                'zone' => $zone,
            ]);
        }
        
        return $this->render('action/confirm.html.twig', [
            'selectedUnits' => $selectedUnits,
            'targetEmpire' => $targetEmpire,
            'dimension' => $dimension,
            'zone' => $zone,
            'travelTime' => $travelTimeInSeconds, // Formater correctement
            'unitNames' => $unitNames,
        ]);
        
    }

    #[Route('/execute-action/{targetEmpireId}/{dimension}/{zone}', name: 'app_execute_action')]
    public function executeAction(
        int $targetEmpireId, 
        int $dimension, 
        int $zone, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $attackerEmpire = $user->getSelectedEmpire();
        $targetEmpire = $entityManager->getRepository(Empire::class)->find($targetEmpireId);
    
        if (!$targetEmpire) {
            throw $this->createNotFoundException('Empire défenseur non trouvé.');
        }
    
        // Récupérer les unités attaquantes
        $selectedUnits = $request->getSession()->get('selectedUnits', []);
        if (empty($selectedUnits)) {
            throw $this->createNotFoundException('Aucune unité sélectionnée pour l\'attaque.');
        }
    
        $attackingArmy = $attackerEmpire->getUnit()->getArmy();
    
        // Soustraire les unités utilisées pour l'attaque de l'armée
        foreach ($selectedUnits as $unitId => $quantity) {
            if (isset($attackingArmy[$unitId])) {
                if ($attackingArmy[$unitId] < $quantity) {
                    $this->addFlash('error', 'Vous ne pouvez pas envoyer plus d\'unités que vous n\'en avez.');
                    return $this->redirectToRoute('app_prepare_action', [
                        'targetEmpireId' => $targetEmpireId,
                        'dimension' => $dimension,
                        'zone' => $zone,
                    ]);
                }
    
                $attackingArmy[$unitId] -= $quantity;
    
                // Supprimer l'entrée si la quantité atteint zéro
                if ($attackingArmy[$unitId] <= 0) {
                    unset($attackingArmy[$unitId]);
                }
            } else {
                $this->addFlash('error', 'Vous ne possédez pas cette unité.');
                return $this->redirectToRoute('app_prepare_action', [
                    'targetEmpireId' => $targetEmpireId,
                    'dimension' => $dimension,
                    'zone' => $zone,
                ]);
            }
        }
    
        // Mettre à jour l'armée de l'empire
        $attackerEmpire->getUnit()->setArmy($attackingArmy);
        $entityManager->persist($attackerEmpire);
    
        // Calcul des dates
        $currentTime = new \DateTime();
        try {
            // Calculer le temps de trajet
            $speedData = $this->speedCalculator->calculSpeed($selectedUnits);
            $travelTimeInSeconds = $speedData['travelTimeInSeconds'];
            $executeTime = $speedData['executeTime'];    
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de calcul de la vitesse : ' . $e->getMessage());
            return $this->redirectToRoute('app_prepare_action', [
                'targetEmpireId' => $targetEmpireId,
                'dimension' => $dimension,
                'zone' => $zone,
            ]);
        }
    
        // Enregistrement du délai dans le log
        $logDetails = "Bataille entre " . $attackerEmpire->getName() . " et " . $targetEmpire->getName() . "\n";
        $logDetails .= "\nDélai avant l'attaque : " . gmdate("H:i:s", $travelTimeInSeconds) . "\n";
        $logDetails .= "Temps actuel : " . $currentTime->format("Y-m-d H:i:s") . "\n";
        $logDetails .= "Temps d'exécution estimé : " . $executeTime->format("Y-m-d H:i:s") . "\n";
    
        // Créer une action différée si le temps d'attente est supérieur à zéro
        if ($travelTimeInSeconds > 0) {
            $action = new Action();
            $action->setName('Attaque');
            $action->setStartTime($currentTime);
            $action->setEndTime($executeTime);
            $action->setArmy($selectedUnits);
            $action->setStatus('En attente');
            $action->setEmpire($attackerEmpire);
            $action->setTarget($targetEmpire);
    
            // Persister l'action
            $entityManager->persist($action);
            $entityManager->flush();
    
            // Retourner une réponse informant l'utilisateur du délai
            $this->addFlash('info', "L'attaque a été programmée. Elle commencera à " . $executeTime->format("H:i:s") . ".");
            return $this->redirectToRoute('app_home');
        }
    
        return $this->redirectToRoute('app_home', [
            'message' => 'L\'attaque a été exécutée avec succès !',
        ]);
    }    
}
