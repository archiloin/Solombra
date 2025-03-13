<?php

namespace App\Controller;

use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/member/profil')]
class ProfilController extends AbstractController
{
    #[Route('/', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }

    #[Route('/edit', name: 'app_profil_edit')]
    public function editProfile(EntityManagerInterface $em, Request $request, ValidatorInterface $validator): Response
    {
        $user = $this->getUser(); // Obtenez l'utilisateur actuel
    
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Validation supplémentaire pour vérifier l'unicité de l'e-mail
            $violations = $validator->validateProperty($user, 'email');
            if (count($violations) > 0) {
                // Informez l'utilisateur que l'e-mail est déjà utilisé
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                // Si aucune violation, procédez à la mise à jour
                $em->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');
                return $this->redirectToRoute('app_profil');
            }
        }
    
        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
