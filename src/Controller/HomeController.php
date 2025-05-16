<?php

// src/Controller/HomeController.php
namespace App\Controller;

use App\Form\HomeSearchForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name : 'Home')]
    public function displayPage(
        Request $request
    ): Response
    {
        $form = $this->createForm(HomeSearchForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $wantedEndingPlace = $form->get('wantedEndingPlace')->getData();

            return $this->redirectToRoute('TravelList', [
                'wantedEndingPlace' => $wantedEndingPlace,
            ]);
        }

        return $this->render('home.html.twig', [
            'form' => $form,
        ]);
    }
}