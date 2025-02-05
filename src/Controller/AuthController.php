<?php

namespace App\Controller;

use App\Entity\JwtToken;
use App\Entity\User;
use App\Form\RegistrationFormType;
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
use App\Service\JwtService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class AuthController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'auth_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation('auth_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@pokedex.com', 'Pokedex'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('auth/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('_profiler_home');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'auth_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('auth_login');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('auth_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('auth_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('auth_login');
    }

    #[Route("/login", name: "auth_login")]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JwtService $jwtService
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            echo $email;

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash('login_error', 'Le combo email/mot de passe est invalide 1.' . $email);

                return $this->render('auth/login.html.twig');
            }

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('login_error', 'Le combo email/mot de passe est invalide 2.');

                return $this->render('auth/login.html.twig');
            }

            $token = $jwtService->createToken($user)->toString();
            $tokenEntity = new JwtToken();
            $tokenEntity->setToken($token);
            $tokenEntity->setExpiresAt(new \DateTimeImmutable('+1 hour'));
            $tokenEntity->setUser($user);

            $entityManager->persist($tokenEntity);
            $entityManager->flush();

            $cookie = Cookie::create('token', $token, time() + 3600, '/', null, false, true);

            $response = $this->redirectToRoute('auth_login');
            $response->headers->setCookie($cookie);

            return $response;
        }

        return $this->render('auth/login.html.twig');
    }

    #[Route("/logout", name: "auth_logout")]
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on the firewall.');
    }
}
