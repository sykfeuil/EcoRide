<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $username = $form->get('username')->getData();
            $email = $form->get('email')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setUsername($username);
            $user->setEmail($email);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('syk.devmail@gmail.com', 'EcoRide'))
                    ->to((string) $user->getEmail())
                    ->subject('Merci de confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('Home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/createEmployee', name: 'app_register_employee')]
    public function registerEmployee(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Uniquement pour l'administrateur
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $username = $form->get('username')->getData();
            $email = $form->get('email')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setRoles(['ROLE_EMPLOYEE']);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('syk.devmail@gmail.com', 'EcoRide'))
                    ->to((string) $user->getEmail())
                    ->subject('Merci de confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('AdminInterface');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
