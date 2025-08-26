<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/competence')]
#[IsGranted('ROLE_ADMIN')]
class CompetenceController extends AbstractController
{
    #[Route('/', name: 'app_competence_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('competence/index.html.twig', [
            'competences' => $competenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $competence = new Competence();
            $competence->setNom($request->request->get('nom'));
            $competence->setDescription($request->request->get('description'));
            $competence->setActif(1);

            $entityManager->persist($competence);
            $entityManager->flush();

            $this->addFlash('success', 'Compétence créée avec succès !');
            return $this->redirectToRoute('app_competence_index');
        }

        return $this->render('competence/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'app_competence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $competence->setNom($request->request->get('nom'));
            $competence->setDescription($request->request->get('description'));
            $competence->setActif($request->request->get('actif') === '1' ? 1 : 0);

            $entityManager->flush();

            $this->addFlash('success', 'Compétence modifiée avec succès !');
            return $this->redirectToRoute('app_competence_index');
        }

        return $this->render('competence/edit.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_competence_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$competence->getId(), $request->request->get('_token'))) {
            $entityManager->remove($competence);
            $entityManager->flush();
            $this->addFlash('success', 'Compétence supprimée avec succès !');
        }

        return $this->redirectToRoute('app_competence_index');
    }

    #[Route('/init', name: 'app_competence_init', methods: ['GET'])]
    public function initCompetences(EntityManagerInterface $entityManager): Response
    {
        $competences = [
            'Maçon', 'Coffreur', 'Ferrailleur', 'Terrassier', 'Conducteur d\'engins',
            'Manœuvre de chantier', 'Grutier', 'Plombier', 'Électricien', 'Peintre en bâtiment',
            'Plâtrier', 'Carreleur', 'Menuisier', 'Parqueteur', 'Serrurier-métallier',
            'Chauffagiste', 'Enduiseur', 'Vitrificateur', 'Solier-moquettiste', 'Staffeur-Ornemaniste'
        ];

        foreach ($competences as $nomCompetence) {
            $competence = new Competence();
            $competence->setNom($nomCompetence);
            $competence->setActif(1);
            $entityManager->persist($competence);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Compétences initialisées avec succès !');

        return $this->redirectToRoute('app_competence_index');
    }
}