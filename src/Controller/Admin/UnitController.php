<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Admin\Unit;
use App\Entity\Admin\Force;
use App\Entity\Admin\ListResource;
use App\Form\Admin\UnitType;
use App\Controller\BaseController;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/unit')]
class UnitController extends BaseController
{
    #[Route(path: '/', name: 'admin_unit')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupération de toutes les unités
        $units = $em->getRepository(Unit::class)->findAll();
    
        // Transmission des unités à la vue
        return $this->render('admin/unit/index.html.twig', [
            'units' => $units,
        ]);
    }    

    #[Route(path: '/new', name: 'admin_unit_new')]
    public function unit(EntityManagerInterface $em, Request $request)
    {
        $user = $this->getUser();
        $unit = new Unit();

        if (empty($unit->getBirthCost())) {
            $unit->setBirthCost(['One' => 0, 'Two' => 0]);
        }
        $form = $this->createForm(UnitType::class, $unit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $unit->setBirthCost([
                'One' => $form->get('birthCostOne')->getData(),
                'Two' => $form->get('birthCostTwo')->getData(),
            ]);
            $em->flush();
            $this->addFlash('success', 'Unité crée avec succès.');
            return $this->redirectToRoute('admin_unit_new');
        }

        return $this->render('admin/unit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'admin_unit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, Unit $unit): Response
    {
        // Extrait les valeurs avant de créer le formulaire
        $birthCost = $unit->getBirthCost();
        $birthCostOne = $birthCost[1] ?? null;
        $birthCostTwo = $birthCost[2] ?? null;

        // Crée le formulaire avec les options
        $form = $this->createForm(UnitType::class, $unit, [
            'birthCostOne' => $birthCostOne,
            'birthCostTwo' => $birthCostTwo,
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $unit->setBirthCost([
                1 => $form->get('birthCostOne')->getData(),
                2 => $form->get('birthCostTwo')->getData(),
            ]);
            $em->flush();
    
            $this->addFlash('success', 'Unité mise à jour avec succès.');
            return $this->redirectToRoute('admin_unit');
        }
    
        return $this->render('admin/unit/edit.html.twig', [
            'unit' => $unit,
            'form' => $form->createView(),
        ]);
    }
    
}
