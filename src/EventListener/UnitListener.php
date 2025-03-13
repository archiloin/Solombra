<?php
namespace App\EventListener;

use App\Service\Units\Manager;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\ORM\EntityManagerInterface;

class UnitListener
{
    private Manager $actionManager;
    private EntityManagerInterface $entityManagerInterface;

    public function __construct(Manager $unitManager, EntityManagerInterface $entityManagerInterface)
    {
        $this->unitManager = $unitManager;
        $this->entityManagerInterface = $entityManagerInterface;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne pas exécuter sur les sous-requêtes pour éviter des vérifications multiples
        if (!$event->isMainRequest()) {
            return;
        }

        // Récupérer la requête depuis l'objet RequestEvent
        $request = $event->getRequest();

        // Vérifier et exécuter les actions si nécessaire
        $this->unitManager->checkAndExecutePendingAction($this->entityManagerInterface, $request);
    }
}
