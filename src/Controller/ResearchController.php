<?php

namespace App\Controller;

use App\Entity\Research;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResearchController extends AbstractController
{
    #[Route(path: '/research', name: 'app_research')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $research = $user->getResearchLevels();

        $maps = $em->getRepository(Research::class)->findAll();
    
        return $this->render('research.html.twig', [
            'maps' => $maps,
            'research' => $research,
        ]);
    }
}
