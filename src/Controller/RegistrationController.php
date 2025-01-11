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

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/base/register', name: 'app_register')]
    public function register(Request $request, UserRepository $users, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public/uploads/')] string $photoDirectory): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            /*   $plainPassword = $form->get('plainPassword')->getData(); */


            $role_util = ["ROLE_UTIL"];
            $role_admin = ["ROLE_ADMIN"];
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    'Aircargo2025'
                )
            );
            if ($user->getRoles() == 'Utilisateur') {
                $user->setRoles($role_util);
            } else {
                $user->setRoles($role_admin);
            }
            $codeClt = md5($user->getId() . date('Y-m'));
            $user->setDateCreation(new \DateTime());


            // Générer le QR code
            $qrCode = new QrCode("client:" . $codeClt);
            $writer = new PngWriter();

            $writer = new PngWriter();
            $qrCodeFilePath = $photoDirectory . 'qrcode_' . $codeClt . '.png';
            $result = $writer->write($qrCode);
            $result->saveToFile($qrCodeFilePath);

            dd($qrCodeFilePath);
            // Sauvegarder le chemin du QR code dans la base
            $user->setQrCodePath('qrcode_' . $codeClt . '.png');
            $user->setUrl($codeClt);
            $entityManager->persist($user);
            $entityManager->flush();
            // generate a signed url and email it to the user
             $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('info@aircargo.gescoflex.com', 'AIR CARGO'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            //  return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'users' => $users->findAll()
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
