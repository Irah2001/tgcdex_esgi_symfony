<?php

namespace App\Controller;

use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'vip_purchase')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        return $this->render('purchase/index.html.twig');
    }

    #[Route('/purchase/subscribe', name: 'vip_subscribe')]
    public function subscribe(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        $roles = $user->getRoles();
        array_push($roles, 'ROLE_VIP');
        $user->setRoles(array_unique($roles));
        $userRepository->save($user, true);

        $this->addFlash('success', 'Congratulations! You are now a VIP Trainer!');
        
        return $this->redirectToRoute('home');
    }
}