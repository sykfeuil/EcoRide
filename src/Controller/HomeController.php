<?php

// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name : 'Accueil')]
    public function displayPage(): Response
    {
        return $this->render('base.html.twig', [
            'pageToRender' => 'home.html.twig'
        ]);
    }
}