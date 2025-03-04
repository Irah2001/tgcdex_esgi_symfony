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

class AuthController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'auth_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $email = $request->request->get('email');
        $referer = $request->headers->get('referer');

        if ($email) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $this->addFlash('register_error', 'This email is already used');

                return $this->redirect($referer);
            }
        }

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
                    ->from(new Address('no-reply@pokepack.com', 'PokéPack Explorer'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('auth/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('auth_login');
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
        $this->addFlash('success', 'Your email address has been verified');

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

            $referer = $request->headers->get('referer');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('login_error', 'Invalid email and/or password');

                return $this->redirect($referer);
            }

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('login_error', 'Invalid email and/or password');

                return $this->redirect($referer);
            }

            $token = $jwtService->createToken($user)->toString();
            $tokenEntity = new JwtToken();
            $tokenEntity->setToken($token);
            $tokenEntity->setExpiresAt(new \DateTimeImmutable('+1 day'));
            $tokenEntity->setUser($user);

            $entityManager->persist($tokenEntity);
            $entityManager->flush();

            $cookie = Cookie::create('token', $token, time() + 86400, '/', null, false, true);

            $redirectUrl = $request->query->get('redirect');
            $response = $redirectUrl && $redirectUrl != "" ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
            $response->headers->setCookie($cookie);

            return $response;
        }

        return $this->render('auth/login.html.twig');
    }

    #[Route("/logout", name: "auth_logout")]
    public function logout()
    {
        $cookie = Cookie::create('token', '', 0, '/', null, false, true);
        $response = $this->redirectToRoute('home');
        $response->headers->setCookie($cookie);

        return $response;
    }
}
