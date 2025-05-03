<?php

// src/Controller/AccountController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\AccountDataForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name : 'Compte')]
    public function displayPage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $email = $this->getUser()->getUserIdentifier();
        $user = $entityManager->getRepository(User::class)->find($email);

        $form = $this->createForm(AccountDataForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $driver = $form->get('driver')->getData();
            $passenger = $form->get('passenger')->getData();
            $smoke = $form->get('smoke')->getData();
            $animal = $form->get('animal')->getData();
            $preferences = $form->get('preferences')->getData();
            $credit = $form->get('credit')->getData();

            $user->setDriver($driver);
            $user->setPassenger($passenger);
            $user->setSmoke($smoke);
            $user->setAnimal($animal);
            $user->setPreferences($preferences);
            $user->setCredit($credit);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('Compte');
        }

        return $this->render('userInterface/userAccount.html.twig', [
            'accountDataForm' => $form,
            'currentUser' => $user,
        ]);
    }
}