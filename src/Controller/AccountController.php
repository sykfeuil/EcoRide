<?php

// src/Controller/AccountController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\AccountDataForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name : 'Account')]
    public function displayPage(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/profilePics')] string $uploadDirectory
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
}