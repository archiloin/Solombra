<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CheckHeroListener
{
    private $router;
    private $security;

    public function __construct(RouterInterface $router, Security $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $user = $this->security->getUser();

        if ($user) {
            $firstEmpire = $user->getEmpires()->first();
        }
    
        // Si l'utilisateur n'est pas connecté ou a déjà un héros, ne faites rien
        if (!$user || $firstEmpire->hasHero()) {
            return;
        }
    
        // Si on est déjà sur la page de choix de héros ou sur la page de connexion, ne faites rien
        $currentRoute = $event->getRequest()->get('_route');
        if (in_array($currentRoute, ['app_hero_starter', 'app_login', 'app_register', 'app_index', 'app_hero_buy_starter', 'update_resource_name'])) {
            return;
        }
    
        $response = new RedirectResponse($this->router->generate('app_hero_starter'));
        $event->setResponse($response);
    }    
}
