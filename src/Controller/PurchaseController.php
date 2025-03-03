<?php

namespace App\Controller;

use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase', name: 'vip_purchase')]
    public function purchase(UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('auth_login');
        }

        $roles = $user->getRoles();
        array_push($roles, 'ROLE_VIP');
        $user->setRoles(array_unique($roles));
        $userRepository->save($user);
        return $this->render('purchase/index.html.twig');
    }
}