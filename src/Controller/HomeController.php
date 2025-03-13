<?php

namespace App\Controller;

use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route(path: '/member', name: 'app_home')]
    public function home(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $selectedEmpire = $user->getSelectedEmpire();
        $hero = $selectedEmpire->getHero();
    
        // Appeler la fonction pour obtenir les bâtiments et les temps associés
        [$buildings, $buildingTimes] = $this->getBuildingsAndTimes($em, $selectedEmpire, $hero);
    
        return $this->render('home.html.twig', [
            'buildings' => $buildings,
            'buildingTimes' => $buildingTimes,
        ]);
    }    
}
