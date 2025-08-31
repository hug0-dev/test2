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
        // Récupérer seulement les ouvriers (pas les admins)
        $ouvriers = $userRepository->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :admin_role')
            ->setParameter('admin_role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        return $this->render('user/index.html.twig', [
            'users' => $ouvriers,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        CompetenceRepository $competenceRepository,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $competences = $competenceRepository->findBy(['actif' => 1]);
            $userCompetences = [];
            
            // Récupérer les compétences actuelles de l'utilisateur
            foreach ($user->getUserCompetences() as $userCompetence) {
                $userCompetences[] = $userCompetence->getCompetence()->getId();
            }

            if ($request->isMethod('POST')) {
                // Vérifier le token CSRF
                if ($this->isCsrfTokenValid('edit' . $user->getId(), $request->request->get('_token'))) {
                // Mise à jour des informations de base
                $nom = $request->request->get('nom');
                $email = $request->request->get('email');
                $equipeId = $request->request->get('equipe_id');
                
                if ($nom) {
                    $user->setNom($nom);
                }
                if ($email) {
                    $user->setEmail($email);
                }
                
                // Gestion de l'équipe
                if ($equipeId) {
                    $equipe = $entityManager->getRepository(\App\Entity\Equipe::class)->find($equipeId);
                    $user->setEquipe($equipe);
                } else {
                    $user->setEquipe(null);
                }

                // Gestion des compétences
                $selectedCompetences = $request->request->all('competences') ?? [];
                
                // Supprimer toutes les compétences actuelles
                foreach ($user->getUserCompetences() as $userCompetence) {
                    $entityManager->remove($userCompetence);
                }
                
                // Flush pour supprimer les anciennes relations
                $entityManager->flush();
                
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
                $this->addFlash('success', 'Utilisateur mis à jour avec succès !');
                
                return $this->redirectToRoute('app_user_index');
            } else {
                $this->addFlash('error', 'Token CSRF invalide.');
            }
        }

            // Récupérer toutes les équipes pour le select
            $equipes = $entityManager->getRepository(\App\Entity\Equipe::class)->findAll();

            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'competences' => $competences,
                'userCompetences' => $userCompetences,
                'equipes' => $equipes,
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            return $this->redirectToRoute('app_user_index');
        }
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