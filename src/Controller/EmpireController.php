<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Empire;
use App\Entity\Resource;
use App\Entity\Admin\ListHero;
use App\Form\RegistrationFormType;
use App\Controller\BaseController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboStream;
use Symfony\UX\Turbo\Stream;

#[Route(path: '/member/empire')]
class EmpireController extends BaseController
{
    #[Route(path: '/new', name: 'app_empire_new')]
    public function empire(EntityManagerInterface $entityManager, Request $request)
    {
        $user = $this->getUser();
        $empire = new Empire();
        $resource = new Resource();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $empire->setUser($user);
            $empire->setName($user->getUsername());
            $empire->setResource($resource);
            $entityManager->persist($resource);
            $entityManager->persist($empire);
        }

        return $this->render('hero/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/select-empire', name: 'app_select_empire_page')]
    public function selectEmpirePage(EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); 

        $repository = $em->getRepository(Empire::class);
        $empires = $repository->findBy(['user' => $user]);

        return $this->render('empire/select.html.twig', [
            'empires' => $empires,
        ]);
    }
   
    #[Route(path: '/select-empire/{empireId}', name: 'app_select_empire')]
    public function selectEmpire(EntityManagerInterface $em, Request $request, int $empireId): Response
    {
        $user = $this->getUser();
        
        $repository = $em->getRepository(Empire::class);
        $empire = $repository->find($empireId);

        $repository = $em->getRepository(Empire::class);
        $selectedEmpire = $repository->findOneBy(['user' => $user, 'selected' => true]);    
        
        // Vérifiez si le héros existe
        if (!$empire) {
            throw $this->createNotFoundException('L\'empire sélectionné n\'existe pas.');
        }

        $selectedEmpire->setSelected(false);
        $empire->setSelected(true);
        $em->flush();
        
        // Redirigez l'utilisateur vers une page de confirmation ou d'accueil
        return $this->redirectToRoute('app_home'); // Assurez-vous d'ajuster la route de redirection selon votre application
    }
    
}
