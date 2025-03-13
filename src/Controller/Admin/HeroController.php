<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Admin\ListHero;
use App\Form\Admin\HeroType;
use App\Controller\BaseController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/hero')]
class HeroController extends BaseController
{
    #[Route(path: '/new', name: 'admin_hero_new')]
    public function hero(EntityManagerInterface $em, Request $request)
    {
        $user = $this->getUser();
        $hero = new ListHero();

        $form = $this->createForm(HeroType::class, $hero);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($hero);
            $em->flush();
            $this->addFlash('success', 'Héro crée avec succès.');
            return $this->redirectToRoute('admin_hero_new');
        }

        return $this->render('admin/hero/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
