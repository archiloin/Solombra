<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends BaseController
{
    #[Route(path: '/', name: 'app_index')]
    public function home()
    {

        return $this->render('vitrine.html.twig');
    }

    #[Route(path: '/kernel', name: 'app_kernel')]
    public function kernel()
    {

        return $this->render('kernel.html.twig');
    }

    #[Route(path: '/cgu', name: 'app_cgu')]
    public function cgu()
    {

        return $this->render('legal/cgu.html.twig');
    }
}
