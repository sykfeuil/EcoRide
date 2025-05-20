<?php

// src/Controller/AccountController.php
namespace App\Controller;

use App\Entity\Opinion;
use App\Entity\Travel;
use App\Entity\User;
use App\Form\AccountDataForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AccountController extends AbstractController
{
    #[Route('/account', name : 'Account')]
    public function displayPage(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/images/uploads')] string $uploadDirectory
    ): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $form = $this->createForm(AccountDataForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $driver = $form->get('driver')->getData();
            $passenger = $form->get('passenger')->getData();
            $smoke = $form->get('smoke')->getData();
            $animal = $form->get('animal')->getData();
            $preferences = $form->get('preferences')->getData();
            $credit = $form->get('credit')->getData();
            $pictureFile = $form->get('picture')->getData();

            // Uniquement quand une image est chargé
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Sécurise le nom de l'url
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Upload du fichier dans le dossier voulu
                try {
                    $pictureFile->move($uploadDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                
                $user->setPicture($newFilename);
            }

            $user->setDriver($driver);
            $user->setPassenger($passenger);
            $user->setSmoke($smoke);
            $user->setAnimal($animal);
            $user->setPreferences($preferences);
            $user->setCredit($credit);

            $entityManager->persist($user);
            $entityManager->flush();

            if ($driver == true) {
                return $this->redirectToRoute('CarList');
            }
            else {
                return $this->redirectToRoute('Account');
            }
        }

        return $this->render('userInterface/userAccount.html.twig', [
            'accountDataForm' => $form,
            'currentUser' => $user,
        ]);
    }

    #[Route('/account/del')]
    public function deletePic(EntityManagerInterface $entityManager): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $user->setPicture('');

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('Account');
    }

    #[Route('/account/employee', name : 'EmployeeInterface')]
    public function employeePage(EntityManagerInterface $entityManager): Response
    {
        // Uniquement pour les employés
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $allOpinions = $entityManager->getRepository(Opinion::class)->findAll();

        return $this->render('userInterface/employeeInterface.html.twig', [
            'currentUser' => $user,
            'allOpinions' => $allOpinions,
        ]);
    }

    #[Route('/account/employee/validateReview/{reviewID}', name : 'ValidateReview')]
    public function validateReview(
        EntityManagerInterface $entityManager,
        int $reviewID
    ): Response
    {
        // Uniquement pour les employés
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        $opinion = $entityManager->getRepository(Opinion::class)->find($reviewID);

        $opinion->setValid(true);

        $entityManager->persist($opinion);
        $entityManager->flush();

        return $this->redirectToRoute('EmployeeInterface');
    }

    #[Route('/account/employee/deleteReview/{reviewID}', name : 'DeleteReview')]
    public function deleteReview(
        EntityManagerInterface $entityManager,
        int $reviewID
    ): Response
    {
        // Uniquement pour les employés
        $this->denyAccessUnlessGranted('ROLE_EMPLOYEE');

        $opinion = $entityManager->getRepository(Opinion::class)->find($reviewID);

        $entityManager->remove($opinion);
        $entityManager->flush();

        return $this->redirectToRoute('EmployeeInterface');
    }

    #[Route('/account/admin', name : 'AdminInterface')]
    public function adminPage(
        EntityManagerInterface $entityManager,
        ChartBuilderInterface $chartBuilder
    ): Response
    {
        // Uniquement pour l'administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        $allUsers = $entityManager->getRepository(User::class)->findAll();

        $allOpinions = $entityManager->getRepository(Opinion::class)->findAll();
        $nbrCreditEarned = 0;

        foreach($allOpinions as $review) {
            if ($review->isValid() == true) {
                $nbrCreditEarned += 2;
            }
        }

        // Création des graphiques
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        
        // Ajout des données pour le graphique
        $firstDayOfMonth = new DateTime();
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = new DateTime();
        $lastDayOfMonth->modify('last day of this month');
        $day = $firstDayOfMonth;

        $firstDayOfMonthTimeStamp = strtotime($firstDayOfMonth->format('d-m-Y'));
        $lastDayOfMonthTimeStamp = strtotime($lastDayOfMonth->format('d-m-Y'));

        $allTravels = $entityManager->getRepository(Travel::class)->findAll();
        
        // Labels des graphiques (tous les jours du mois en cours)
        $chartLabels = [];
        // Données du premier graphique
        $travelPerDayData = [];
        // Données du deuxième graphique
        $creditPerDayData = [];
        while ($day < $lastDayOfMonth) {
            array_push($chartLabels, $day->format('d/m/Y'));
            array_push($travelPerDayData, 0);
            array_push($creditPerDayData, 0);
            $day->modify('+1day');
        }

        foreach($allTravels as $travel) {
            // Vérification si le covoiturage est compris dans le mois en cours
            $travelTimeStamp = strtotime($travel->getStartingDate()->format('d-m-Y'));
            if ($travelTimeStamp >= $firstDayOfMonthTimeStamp && $travelTimeStamp <= $lastDayOfMonthTimeStamp) {
                $dayInTheMonth = (int)$travel->getStartingDate()->format('d');

                // +1 voyage pour le jour choisi
                $travelPerDayData[$dayInTheMonth-1] += 1;

                // +2 crédit par avis valide pour le jour choisi
                foreach($travel->getOpinions() as $review) {
                    if ($review->isValid() == true) {
                        $creditPerDayData[$dayInTheMonth-1] += 2;
                    }
                }
            }
        }

        $chart->setData([
            'labels' => $chartLabels,
            'datasets' => [
                [
                    'label' => 'Nombre de covoiturages',
                    'backgroundColor' => 'rgb(0, 138, 216)',
                    'borderColor' => 'rgb(0, 138, 216)',
                    'data' => $travelPerDayData,
                ],
                [
                    'label' => 'Nombre de crédits gagnés',
                    'backgroundColor' => 'rgb(254,221,0)',
                    'borderColor' => 'rgb(254,221,0)',
                    'data' => $creditPerDayData,
                ],
            ],
        ]);

        return $this->render('userInterface/adminInterface.html.twig', [
            'currentUser' => $user,
            'allUsers' => $allUsers,
            'chart' => $chart,
            'nbrCreditEarned' => $nbrCreditEarned,
        ]);
    }

    #[Route('/account/admin/deleteUser/{userID}', name : 'DeleteUser')]
    public function deleteUser(
        EntityManagerInterface $entityManager,
        int $userID
    ): Response
    {
        // Uniquement pour l'administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userToDelete = $entityManager->getRepository(User::class)->find($userID);

        $entityManager->remove($userToDelete);
        $entityManager->flush();

        return $this->redirectToRoute('AdminInterface');
    }
}