<?php

// src/Controller/CarController.php
namespace App\Controller;

use App\Entity\Car;
use App\Entity\User;
use App\Form\CarForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CarController extends AbstractController
{
    #[Route('/cars', name : 'CarList')]
    public function displayPage(
        EntityManagerInterface $entityManager
    ): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $id = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($id);

        // Uniquement pour les conducteurs
        if($user->isDriver() == false) {
            return $this->redirectToRoute('Account');
        }

        $carList = $user->getCars();
        if ($carList->isEmpty()) {
            return $this->redirectToRoute('CreateCar');
        }

        return $this->render('userInterface/carList.html.twig', [
            'carList' => $carList,
            'currentUser' => $user,
        ]);
    }

    #[Route('/cars/create', name : 'CreateCar')]
    public function createCar(
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
            return $this->redirectToRoute('Account');
        }

        $form = $this->createForm(CarForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $car = new Car();

            $SerialNumber = $form->get('SerialNumber')->getData();
            $SerialDate = $form->get('SerialDate')->getData();
            $Type = $form->get('Type')->getData();
            $Color = $form->get('Color')->getData();
            $Brand = $form->get('Brand')->getData();
            $Seats = $form->get('Seats')->getData();
            $Electrical = $form->get('Electrical')->getData();

            $car->setSerialNumber($SerialNumber);
            $car->setSerialDate($SerialDate);
            $car->setType($Type);
            $car->setColor($Color);
            $car->setBrand($Brand);
            $car->setSeats($Seats);
            $car->setElectrical($Electrical);

            $user->addCar($car);

            $entityManager->persist($car);
            $entityManager->flush();

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('CarList');
        }

        return $this->render('userInterface/createCar.html.twig', [
            'carForm' => $form,
            'currentUser' => $user,
        ]);
    }

    #[Route('/cars/delete/{carID}', name : 'DeleteCar')]
    public function deleteCar(
        int $carID,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Uniquement pour les utilisateurs
        $this->denyAccessUnlessGranted('ROLE_USER');

        $carToDelete = $entityManager->getRepository(Car::class)->find($carID);
        
        $entityManager->remove($carToDelete);
        $entityManager->flush();

        return $this->redirectToRoute('CarList');
    }
}