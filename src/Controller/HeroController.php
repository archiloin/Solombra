<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Building;
use App\Entity\BuildingLevel;
use App\Entity\Hero;
use App\Entity\Map;
use App\Entity\Unit;
use App\Entity\Admin\ListHero;
use App\Entity\Empire;
use App\Entity\Resource;
use App\Entity\Admin\ListResource;
use App\Controller\BaseController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/member/hero')]
class HeroController extends BaseController
{
    #[Route(path: '/starter', name: 'app_hero_starter')]
    public function starter(EntityManagerInterface $em)
    {
        $repository = $em->getRepository(ListHero::class);
        $heroes = $repository->findAll();

        return $this->render('hero/starter.html.twig', [
            'heroes' => $heroes,
        ]);
    }

    #[Route(path: '/new/{dimension}/{zone}', name: 'app_hero_new')]
    public function hero(int $dimension, int $zone, EntityManagerInterface $em)
    {
        if ($dimension >1000000000) {
            $this->addFlash('error', 'La dimension doit être inférieur à 1 milliard');
            return $this->redirectToRoute('app_map', ['dimension'=> 1] );
        }
        $user = $this->getUser();

        $repository = $em->getRepository(ListHero::class);
        $heroes = $repository->findAll();

        return $this->render('hero/new.html.twig', [
            'heroes' => $heroes,
            'dimension' => $dimension,
            'zone' => $zone,
        ]);
    }

    #[Route(path: '/buy/{heroId}', name: 'app_hero_buy_starter')]
    public function buyStarter(
        EntityManagerInterface $em, 
        int $heroId,
        ): Response
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
    
        // Récupérer le héros à acheter
        $listHero = $em->getRepository(ListHero::class)->find($heroId);
        if (!$listHero) {
            // Si le héros n'existe pas, affichez un message d'erreur
            $this->addFlash('error', 'Le héros demandé n\'existe pas.');
            return $this->redirectToRoute('app_hero_new');
        }
    
        // Vérifier si l'utilisateur peut se permettre d'acheter le héros
        if ($user->getSolumns() < $listHero->getCost()) {
            $this->addFlash('error', 'Vous n\'avez pas suffisamment de solumns pour acheter ce héros.');
            return $this->redirectToRoute('app_hero_new');
        }

        $hero = new Hero();
        $hero->setName($listHero->getName());
        $hero->setInfo($listHero);
        $em->persist($hero);
        $selectedEmpire->setHero($hero);

        // Déduire le coût du héros des solumns de l'utilisateur
        $user->setSolumns($user->getSolumns() - $listHero->getCost());

        // Sauvegarder les changements
        $em->flush();
    
        // Ajouter un message flash de succès
        $this->addFlash('success', 'Héros acheté avec succès !');
    
        // Rediriger l'utilisateur
        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/buy/{heroId}/{dimension}/{zone}', name: 'app_hero_buy')]
    public function buyHero(
        EntityManagerInterface $em, 
        int $heroId,
        int $dimension,
        int $zone,
        ): Response
    {
        if ($dimension >1000000000) {
            $this->addFlash('error', 'La dimension doit être inférieur à 1 milliard');
            return $this->redirectToRoute('app_map', ['dimension'=> 1] );
        }
        $user = $this->getUser();
    
        // Récupérer le héros à acheter
        $listHero = $em->getRepository(ListHero::class)->find($heroId);
        if (!$listHero) {
            // Si le héros n'existe pas, affichez un message d'erreur
            $this->addFlash('error', 'Le héros demandé n\'existe pas.');
            return $this->redirectToRoute('app_hero_new');
        }
    
        // Vérifier si l'utilisateur peut se permettre d'acheter le héros
        if ($user->getSolumns() < $listHero->getCost()) {
            $this->addFlash('error', 'Vous n\'avez pas suffisamment de solumns pour acheter ce héros.');
            return $this->redirectToRoute('app_hero_new');
        }
    
        // Désélectionner l'empire actuel si il y en a un
        $currentSelectedEmpire = $em->getRepository(Empire::class)->findOneBy(['user' => $user, 'selected' => true]);
        if ($currentSelectedEmpire) {
            $currentSelectedEmpire->setSelected(false);
            $em->persist($currentSelectedEmpire);
        }
        
        // Rechercher un empire de l'utilisateur sans héros
        $empires = $user->getEmpires();
        $existingEmpire = null;
    
        foreach ($empires as $empire) {
            if (!$empire->getHero()) {
                $existingEmpire = $empire;
                break;
            }
        }

        $hero = new Hero();
        $hero->setName($listHero->getName());
        $hero->setInfo($listHero);
        $em->persist($hero);
        $unit = new Unit();

        $mapZone = $em->getRepository(Map::class)->findOneBy([
            'id' => $zone,
        ]);

        // Supposons que $mapZone est déjà défini et correspond à l'objet Map pour la zone spécifiée
        $existingEmpireWithZoneAndDimension = $em->getRepository(Empire::class)->findOneBy([
            'zone' => $mapZone,
            'dimension' => $dimension
        ]);

        if ($existingEmpireWithZoneAndDimension) {
            // Si un empire existant est trouvé avec la même zone et la même dimension,
            // cela signifie que la zone appartient déjà à un empire dans la même dimension.
            $this->addFlash('error', 'Cette zone appartient déjà à un autre empire dans la même dimension.');
            return $this->redirectToRoute('app_hero_buy', ['dimension' => $dimension, 'zone' => $zone]);
        }
        
        // Si un empire sans héros existe, mettez à jour cet empire
        if ($existingEmpire) {
            $existingEmpire->setHero($hero);
            $existingEmpire->setUnit($unit);
            $existingEmpire->setSelected(true);
            $existingEmpire->setDimension($dimension);
            $existingEmpire->setZone($mapZone);
            $em->persist($existingEmpire);
            $empire = $existingEmpire;
        } else {
            // S'il n'y a pas d'empire sans héros, créez-en un nouveau
            $listResources = $em->getRepository(ListResource::class)->findAll();
            $newEmpire = new Empire();
            $newEmpire->setUser($user);
            $newEmpire->setName('à définir'); // Vous pouvez définir le nom comme vous le souhaitez
            $newEmpire->setHero($hero);
            $newEmpire->setUnit($unit);
            $newEmpire->setDimension($dimension);
            $newEmpire->setZone($mapZone);
            $newEmpire->setSelected(true);
            
            $listResources = $em->getRepository(ListResource::class)->findAll();
            $resource = new Resource();
            $resource->setInfo($listResources[0]);
            $resource->setName('Or');
            $resource->setQuantity(300);
            $resource->setEmpire($newEmpire);
            $em->persist($resource);

            $resource = new Resource();
            $resource->setInfo($listResources[1]);
            $resource->setName('Argent');
            $resource->setQuantity(300);
            $resource->setEmpire($newEmpire);
            $em->persist($resource);
            
            $em->persist($newEmpire);
            $empire = $newEmpire;

            $repository = $em->getRepository(Building::class);
            $buildings = $repository->findAll(); // Trouver tous les bâtiments de base
            foreach ($buildings as $building) {
                $buildingLevel = new BuildingLevel();
                $buildingLevel->setBuilding($building);
                $buildingLevel->setEmpire($empire);
                $buildingLevel->setLevel(1); // Niveau initial
        
                $em->persist($buildingLevel);
            }
        }

        // Déduire le coût du héros des solumns de l'utilisateur
        $user->setSolumns($user->getSolumns() - $listHero->getCost());

        // Sauvegarder les changements
        $em->flush();
    
        // Ajouter un message flash de succès
        $this->addFlash('success', 'Héros acheté avec succès !');
    
        // Rediriger l'utilisateur
        return $this->redirectToRoute('app_home');
    }
}
