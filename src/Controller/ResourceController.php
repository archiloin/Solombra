<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResourceController extends AbstractController
{
    #[Route('/resource/update/{empireId}', name: 'resource_update', methods: ['POST'])]
    public function update(Request $request, int $empireId): Response
    {


        return true;
    }
}
