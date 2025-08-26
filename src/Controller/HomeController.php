<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Redirection selon le rôle
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Si c'est un admin, rediriger vers le dashboard admin
            return $this->render('home/admin_dashboard.html.twig', [
                'user' => $user,
            ]);
        } else {
            // Si c'est un ouvrier, rediriger vers son dashboard
            return $this->redirectToRoute('app_ouvrier_dashboard');
        }
    }
}