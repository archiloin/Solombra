<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/unsubscribe', name: 'app_unsubscribe')]
    public function unsubscribe(EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();

        if ($user) {
            // Suppression de l'utilisateur de la base de données
            $em->remove($user);
            $em->flush();

            // Ajouter un message flash, si nécessaire
            $this->addFlash('success', 'Votre compte a été supprimé.');

            // Déconnecter l'utilisateur
            return $this->redirectToRoute('app_logout');
        }

        // Rediriger vers la page d'accueil si l'utilisateur n'est pas trouvé ou déjà déconnecté
        return $this->redirectToRoute('app_home');
    }
}
