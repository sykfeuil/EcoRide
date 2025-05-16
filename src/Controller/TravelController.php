<?php

// src/Controller/TravelController.php
namespace App\Controller;

use App\Entity\Opinion;
use App\Entity\Travel;
use App\Entity\User;
use App\Form\ReviewForm;
use App\Form\TravelFilterForm;
use App\Form\TravelForm;
use App\Form\TravelJoinConfirmForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class TravelController extends AbstractController
{
    #[Route('/travels/{wantedEndingPlace}', name : 'TravelList')]
    public function displayTravels(
        Request $request,
        EntityManagerInterface $entityManager,
        string $wantedEndingPlace = ""
    ): Response
    {

        $travelList = [];

        $form = $this->createForm(TravelFilterForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $endingDate = $form->get('endingDate')->getData();
            $startingPlace = $form->get('startingPlace')->getData();
            $endingPlace = $form->get('endingPlace')->getData();
            $ecoFilter = $form->get('ecoFilter')->getData();
            $maxPriceFilter = $form->get('maxPriceFilter')->getData();
            $timeFilter = $form->get('timeFilter')->getData();
            $markFilter = $form->get('markFilter')->getData();
            
            if ($startingPlace == "") {
                $travelList = $entityManager->getRepository(Travel::class)->findBy(
                    ['endingPlace' => $endingPlace, 'endingDate' => $endingDate]
                );
            }
            else {
                $travelList = $entityManager->getRepository(Travel::class)->findBy(
                    ['startingPlace' => $startingPlace, 'endingPlace' => $endingPlace, 'endingDate' => $endingDate]
                );
            }

            // Filtre voyage écologique
            if ($ecoFilter == 1) {
                $travelListTemp = $travelList;
                $travelList = [];
                foreach($travelListTemp as $travel) {
                    if ($travel->getCar()->isElectrical() == 1) {
                        array_push($travelList, $travel);
                    }
                }
            }

            // Filtre prix maximum
            if ($maxPriceFilter != null) {
                $travelListTemp = $travelList;
                $travelList = [];
                foreach($travelListTemp as $travel) {
                    if ($travel->getPrice() <= $maxPriceFilter) {
                        array_push($travelList, $travel);
                    }
                }
            }

            // Filtre durée maximum
            if ($timeFilter != null) {
                $travelListTemp = $travelList;
                $travelList = [];
                foreach($travelListTemp as $travel) {

                    $startDateTime = $travel->getStartingDate();
                    $startHour = $travel->getStartingHour()->format('h');
                    $startDateTime->modify('+'.$startHour.'hours');

                    $endDateTime = $travel->getEndingDate();
                    $endHour = $travel->getEndingHour()->format('h');
                    $endDateTime->modify('+'.$endHour.'hours');

                    $diff = $endDateTime->diff($startDateTime);
                    $totHour = $diff->format('%d') * 24 + $diff->format('%h');

                    if ($totHour <= $timeFilter) {
                        array_push($travelList, $travel);
                    }
                }
            }

            // Filtre note minimale
            if ($markFilter != null) {
                $travelListTemp = $travelList;
                $travelList = [];
                foreach($travelListTemp as $travel) {
                    if ($travel->getDriver()->getMark() >= $markFilter) {
                        array_push($travelList, $travel);
                    }
                }
            }
        }
        else {
            // Accès par la barre de recherche de l'accueil
            if ($wantedEndingPlace != "") {
                $today = new DateTime();
                $travelList = $entityManager->getRepository(Travel::class)->findBy(
                    ['endingPlace' => $wantedEndingPlace, 'endingDate' => $today]
                );

                // Valeur par défaut des filtres
                $form->setData([
                    'endingPlace' => $wantedEndingPlace,
                    'endingDate' => $today
                ]);
            }
        }

        return $this->render('travelInterface/travelList.html.twig', [
            'filterForm' => $form,
            'travelList' => $travelList
        ]);
    }

    #[Route('/travels/detail/{travelID}', name : 'TravelDetail')]
    public function displayDetails(
        int $travelID,
        EntityManagerInterface $entityManager
    ): Response
    {
        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        return $this->render('travelInterface/travelDetail.html.twig', [
            'travel' => $travel
        ]);
    }

    #[Route('/travels/start/{travelID}', name : 'TravelStart')]
    public function startTravel(
        int $travelID,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        // Voyage en cours
        $travel->setCurrentState(2);

        $entityManager->persist($travel);
        $entityManager->flush();

        return $this->redirectToRoute('History');
    }

    #[Route('/travels/end/{travelID}', name : 'TravelEnd')]
    public function endTravel(
        int $travelID,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        // Voyage en cours
        $travel->setCurrentState(3);

        $entityManager->persist($travel);
        $entityManager->flush();

        return $this->redirectToRoute('History');
    }

    #[Route('/travels/review/{travelID}', name : 'ReviewTravel')]
    public function reviewTravel(
        int $travelID,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        $driver = $travel->getDriver();

        $form = $this->createForm(ReviewForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $opinion = new Opinion();

            $goodTravel = $form->get('goodTravel')->getData();
            $review = $form->get('review')->getData();

            $opinion->setMark($goodTravel);
            $opinion->setReview($review);
            $opinion->setDriver($driver);
            $opinion->setValid(false);

            $entityManager->persist($opinion);
            $entityManager->flush();

            // Si la note est positive alors on met à jour les crédits
            if ($goodTravel == true) {

                $newCredit = $driver->getCredit() + $travel->getPrice() - 2;
                $driver->setCredit($newCredit);

                $entityManager->persist($driver);
                $entityManager->flush();
            }

            return $this->redirectToRoute('History');
        }

        return $this->render('travelInterface/reviewTravel.html.twig', [
            'reviewForm' => $form
        ]);
    }

    #[Route('/travels/join/{travelID}', name : 'JoinTravel')]
    public function joinTravel(
        int $travelID,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        // Le chauffeur participe déjà à son propre trajet
        if ($travel->getDriver()->getId() == $user->getId()) {
            return $this->redirectToRoute('TravelDetail', [
                'travelID' => $travelID,
            ]);
        }

        // L'utilisateur participe déjà à ce trajet
        foreach($travel->getPassengers() as $passenger) {
            if ($passenger->getId() == $user->getId()) {
                return $this->redirectToRoute('TravelDetail', [
                    'travelID' => $travelID,
                ]);
            }
        }

        // Uniquement si suffisamment de place
        if ($travel->getAvailableSeats() < 1) {
            return $this->redirectToRoute('TravelDetail', [
                'travelID' => $travelID,
            ]);
        }

        // Uniquement si suffisamment de crédits
        if ($user->getCredit() < $travel->getPrice()) {
            return $this->redirectToRoute('TravelDetail', [
                'travelID' => $travelID,
            ]);
        }

        $form = $this->createForm(TravelJoinConfirmForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $newCredit = $user->getCredit() - $travel->getPrice();
            $user->setCredit($newCredit);

            $user->addTravelAsPassenger($travel);

            $newAvailableSeats = $travel->getAvailableSeats() - 1;
            $travel->setAvailableSeats($newAvailableSeats);

            $entityManager->persist($travel);
            $entityManager->flush();

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('History');
        }

        return $this->render('travelInterface/joinConfirmation.html.twig', [
            'travel' => $travel,
            'confirmForm' => $form
        ]);
    }

    #[Route('/travels/cancel/{travelID}', name : 'CancelTravel')]
    public function cancelTravel(
        int $travelID,
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $travel = $entityManager->getRepository(Travel::class)->find($travelID);

        // Le chauffeur annule le covoiturage
        if ($travel->getDriver()->getId() == $user->getId()) {

            // Remboursement des passagers et annulation du lien avec le voyage
            foreach($travel->getPassengers() as $passenger) {

                $newCredit = $passenger->getCredit() + $travel->getPrice();
                $passenger->setCredit($newCredit);
                $passenger->removeTravelAsPassenger($travel);

                $entityManager->persist($passenger);
                $entityManager->flush();

                $mailer->send(
                    (new Email())
                        ->from('syk.devmail@gmail.com')
                        ->to('sykfeuillegaming@gmail.com')
                        ->subject('Hello world')
                        ->html('<strong>it works!</strong>')
                );
            }

            // Une fois tous les passagers remboursés on détruit l'objet
            $entityManager->remove($travel);
            $entityManager->flush();
        }

        // Un passager annule son voyage
        foreach($travel->getPassengers() as $passenger) {
            if ($passenger->getId() == $user->getId()) {

                $newAvailableSeats = $travel->getAvailableSeats() + 1;
                $travel->setAvailableSeats($newAvailableSeats);

                $newCredit = $passenger->getCredit() + $travel->getPrice();
                $passenger->setCredit($newCredit);
                $passenger->removeTravelAsPassenger($travel);

                $entityManager->persist($passenger);
                $entityManager->flush();

                $entityManager->persist($travel);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('History');
    }

    #[Route('/travels/create', name : 'CreateTravel')]
    public function createTravel(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        // Uniquement pour les conducteurs
        if($user->isDriver() == false) {
            return $this->redirectToRoute('History');
        }

        $form = $this->createForm(TravelForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $travel = new Travel();

            $startingDate = $form->get('startingDate')->getData();
            $startingHour = $form->get('startingHour')->getData();
            $endingDate = $form->get('endingDate')->getData();
            $endingHour = $form->get('endingHour')->getData();
            $startingPlace = $form->get('startingPlace')->getData();
            $endingPlace = $form->get('endingPlace')->getData();
            $price = $form->get('price')->getData();
            $car = $form->get('car')->getData();
            $availableSeats = $car->getSeats();

            $travel->setStartingDate($startingDate);
            $travel->setStartingHour($startingHour);
            $travel->setEndingDate($endingDate);
            $travel->setEndingHour($endingHour);
            $travel->setStartingPlace($startingPlace);
            $travel->setEndingPlace($endingPlace);
            $travel->setPrice($price);
            $travel->setCar($car);
            $travel->setAvailableSeats($availableSeats);
            $travel->setCurrentState(1);

            $user->addTravelAsDriver($travel);

            $entityManager->persist($travel);
            $entityManager->flush();

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('History');
        }

        return $this->render('travelInterface/travelCreate.html.twig', [
            'travelForm' => $form,
            'currentUser' => $user,
        ]);
    }
}