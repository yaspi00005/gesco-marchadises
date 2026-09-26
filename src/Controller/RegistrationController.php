<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin')]
class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/base/register', name: 'app_register')]
    public function addUser(
        Request $request,
        UserRepository $UserRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du rôle et du pays
            $selectedRole = $form->get('roles')->getData();
            $selectedPays = $request->request->get('pays'); // Récupérer le pays sélectionné

            $roleRamassage = ["ROLE_RAMASSAGE"];
            $roleRepresentant = ["ROLE_REPRESENTANT"];
            $roleAdmin = ["ROLE_ADMIN"];

            // Attribution du rôle principal
            if ($selectedRole == 'Ramasseur') {
                $user->setRoles($roleRamassage);
            } elseif ($selectedRole == 'Représentant') {
                $roles = $roleRepresentant;

                // Ajouter le rôle du pays
                if ($selectedPays == 'Mali') {
                    $roles[] = "ROLE_MALI";
                } elseif ($selectedPays == 'Sénégal') {
                    $roles[] = "ROLE_SENEGAL";
                }

                $user->setRoles($roles);
            } else {
                $user->setRoles($roleAdmin);
            }

            // Hash du mot de passe (mettre un mot de passe par défaut vide)
            $user->setPassword($userPasswordHasher->hashPassword($user, 'Sotrama2025'));
            $user->setDateCreation(new \DateTime());

            $user->setUrl(uniqid());
            // Sauvegarde de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

           
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'users' => $UserRepository->findAll()
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
