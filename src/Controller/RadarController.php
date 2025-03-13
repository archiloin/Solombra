<?php
namespace App\Controller;

use App\Entity\Action;
use App\Entity\Resource;
use App\Repository\Admin\UnitRepository;
use App\Repository\Admin\ListResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RadarController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/radar', name: 'app_radar')]
    public function index(
        EntityManagerInterface $entityManager,
        UnitRepository $unitRepository,
        ListResourceRepository $resourceRepository
    ): Response {
        $user = $this->getUser();
        $userEmpire = $user->getSelectedEmpire();
    
        // Récupérer les actions où l'utilisateur est l'initiateur
        $initiatedActions = $entityManager->getRepository(Action::class)
            ->createQueryBuilder('a')
            ->where('a.empire = :empire')
            ->andWhere('a.status = :statusPending OR a.status = :statusReturn')
            ->setParameter('empire', $userEmpire)
            ->setParameter('statusPending', 'En attente')
            ->setParameter('statusReturn', 'Retour')
            ->getQuery()
            ->getResult();
    
        // Ajouter les détails formatés pour chaque action initiée
        foreach ($initiatedActions as $action) {
            $action->formattedDetails = $this->formatActionDetails($action, $unitRepository, $resourceRepository);
        }
    
        // Récupérer les actions où l'utilisateur est la cible
        $targetedActions = $entityManager->getRepository(Action::class)
            ->findBy(['target' => $userEmpire, 'status' => 'En attente']);
    
        // Ajouter les détails formatés pour chaque action ciblée
        foreach ($targetedActions as $action) {
            $action->formattedDetails = $this->formatActionDetails($action, $unitRepository, $resourceRepository);
        }
    
        return $this->render('radar/index.html.twig', [
            'initiatedActions' => $initiatedActions,
            'targetedActions' => $targetedActions,
        ]);
    }    

    private function formatActionDetails(
        Action $action,
        UnitRepository $unitRepository,
        ListResourceRepository $resourceRepository,
    ): string {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
        // Désérialisation et décodage sécurisés
        $army = $this->safeUnserialize($action->getArmy());
        $lootedResources = $this->safeJsonDecode($action->getLootedResources());

        // Récupérer les noms des unités
        $formattedArmy = "Forces initiales de l'Attaquant :\n";
        foreach ($army as $unitId => $quantity) {
            $unit = $unitRepository->find($unitId);
            $unitName = $unit ? $unit->getName() : 'Unité inconnue';
            $formattedArmy .= "- $unitName: $quantity\n";
        }

        // Récupérer les noms des ressources pillées
        $formattedResources = "";
        foreach ($lootedResources as $resourceId => $quantity) {
            $resourceName = $this->entityManager->getRepository(Resource::class)->findOneBy(['info' => $resourceId, 'empire' => $selectedEmpire])->getName();
            $resource = $resourceRepository->find($resourceId);
            $formattedResources .= "- $resourceName : $quantity pillé\n";
        }

        return $formattedArmy . "\n" . $formattedResources;
    }

    private function safeUnserialize($data): array
    {
        if (is_string($data)) {
            return unserialize($data) ?: []; // Retourne un tableau vide si la désérialisation échoue
        }

        if (is_array($data)) {
            return $data; // Déjà un tableau
        }

        return []; // Valeur par défaut
    }

    private function safeJsonDecode($data): array
    {
        if (is_string($data)) {
            return json_decode($data, true) ?: []; // Retourne un tableau vide si le décodage échoue
        }

        if (is_array($data)) {
            return $data; // Déjà un tableau
        }

        return []; // Valeur par défaut
    }
}
