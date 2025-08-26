<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Competence;
use App\Entity\UserCompetence;
use App\Repository\UserRepository;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/competences', name: 'app_user_manage_competences', methods: ['GET', 'POST'])]
    public function manageCompetences(
        Request $request,
        User $user,
        CompetenceRepository $competenceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $competences = $competenceRepository->findBy(['actif' => 1]);
        $userCompetences = [];
        foreach ($user->getUserCompetences() as $userCompetence) {
            $userCompetences[] = $userCompetence->getCompetence()->getId();
        }

        if ($request->isMethod('POST')) {
            $selectedCompetences = $request->request->all('competences') ?? [];
            
            // Supprimer toutes les compétences actuelles
            foreach ($user->getUserCompetences() as $userCompetence) {
                $entityManager->remove($userCompetence);
            }
            
            // Ajouter les nouvelles compétences
            foreach ($selectedCompetences as $competenceId) {
                $competence = $competenceRepository->find($competenceId);
                if ($competence) {
                    $userCompetence = new UserCompetence();
                    $userCompetence->setUser($user);
                    $userCompetence->setCompetence($competence);
                    $entityManager->persist($userCompetence);
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Compétences mises à jour avec succès !');
            
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/manage_competences.html.twig', [
            'user' => $user,
            'competences' => $competences,
            'userCompetences' => $userCompetences,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}