<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Building;
use App\Entity\BuildingLevel;
use App\Entity\Empire;
use App\Entity\Map;
use App\Entity\Research;
use App\Entity\Resource;
use App\Entity\Unit;
use App\Entity\Admin\ListResource;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use App\Security\EmailVerifier;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $empire = new Empire();
            
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $repository = $entityManager->getRepository(Building::class);
            $buildings = $repository->findAll(); // Trouver tous les bâtiments de base

            $username = $user->getUserName();
            $empire->setName($username);
            $empire->setUser($user);
            $empire->setSelected(true);
            $unit = new Unit();
            $empire->setUnit($unit);
            $listResources = $entityManager->getRepository(ListResource::class)->findAll();
                $resource = new Resource();
                $resource->setInfo($listResources[0]);
                $resource->setName('Or');
                $resource->setQuantity(33000);
                $resource->setEmpire($empire);
                $entityManager->persist($resource);

                $resource = new Resource();
                $resource->setInfo($listResources[1]);
                $resource->setName('Argent');
                $resource->setQuantity(33000);
                $resource->setEmpire($empire);
                $entityManager->persist($resource);

            foreach ($buildings as $building) {
                $buildingLevel = new BuildingLevel();
                $buildingLevel->setBuilding($building);
                $buildingLevel->setEmpire($empire);
                $buildingLevel->setLevel(1); // Niveau initial
        
                $entityManager->persist($buildingLevel);
            }
            // Attribution de la dimension et de la zone à l'empire
            $empireRepository = $entityManager->getRepository(Empire::class);
            $dimension = 1;
            $zoneFound = false;

            while (!$zoneFound) {
                for ($zone = 1; $zone <= 4; $zone++) {
                    if (!$empireRepository->findOneBy(['dimension' => $dimension, 'zone' => $zone])) {
                        $map = $entityManager->getRepository(Map::class)->find($zone);
                        $empire->setDimension($dimension);
                        $empire->setZone($map);
                        $zoneFound = true;
                        break;
                    }
                }
                if (!$zoneFound) {
                    $dimension++;
                }
            }
            $entityManager->persist($user);
            $entityManager->persist($empire);

            $entityManager->flush();
            
            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@solombra.fr', 'Mail Registration'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre adresse e-mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route(path: '/member/hero', name: 'app_registration_hero')]
    public function hero()
    {

        return $this->render('hero.html.twig');
    }
}
