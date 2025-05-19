<?php

// src/Controller/HistoryController.php
namespace App\Controller;

use App\Entity\Travel;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name : 'History')]
    public function displayPage(
        EntityManagerInterface $entityManager
    ): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);
        
        $travelList = $entityManager->getRepository(Travel::class)->findAll();

        // Affichage des voyages où l'utilisateur en cours est le chauffeur
        $travelListTemp = $travelList;
        $travelList = [];

        foreach($travelListTemp as $travel) {
            if ($travel->getDriver()->getId() == $id) {
                array_push($travelList, $travel);
            }
        }

        foreach($travelListTemp as $travel) {
            foreach($travel->getPassengers() as $passenger) {
                if ($passenger->getId() == $id) {
                    array_push($travelList, $travel);
                }
            }
        }

        return $this->render('userInterface/travelHistory.html.twig', [
            'currentUser' => $user,
            'travelList' => $travelList
        ]);
    }
}