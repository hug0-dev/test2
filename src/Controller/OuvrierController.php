<?php

namespace App\Controller;

use App\Repository\AffectationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrier')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class OuvrierController extends AbstractController
{
    #[Route('/', name: 'app_ouvrier_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        
        // Vérifier si c'est bien un ouvrier (pas un admin)
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('ouvrier/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/mes-affectations', name: 'app_ouvrier_affectations', methods: ['GET'])]
    public function mesAffectations(AffectationRepository $affectationRepository): Response
    {
        $user = $this->getUser();
        
        // Vérifier si c'est bien un ouvrier (pas un admin)
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer les affectations via l'équipe de l'utilisateur
        $affectations = [];
        if ($user->getEquipe()) {
            $affectations = $affectationRepository->findBy(['equipe' => $user->getEquipe()]);
        }

        return $this->render('ouvrier/mes_affectations.html.twig', [
            'affectations' => $affectations,
            'user' => $user,
        ]);
    }

    #[Route('/mon-profil', name: 'app_ouvrier_profil', methods: ['GET'])]
    public function monProfil(): Response
    {
        $user = $this->getUser();
        
        // Vérifier si c'est bien un ouvrier (pas un admin)
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('ouvrier/profil.html.twig', [
            'user' => $user,
        ]);
    }
}