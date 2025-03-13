<?php
namespace App\EventListener;

use App\Service\ActionScheduler;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\ORM\EntityManagerInterface;

class ActionListener
{
    private ActionScheduler $actionManager;
    private EntityManagerInterface $entityManagerInterface;

    public function __construct(ActionScheduler $actionManager, EntityManagerInterface $entityManagerInterface)
    {
        $this->actionManager = $actionManager;
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
        $this->actionManager->executeScheduledActions($this->entityManagerInterface, $request);
    }
}
