<?php

namespace App\Controller;

use App\Entity\Competence;
use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CompetenceController extends AbstractController
{
    // Liste des compétences pour un héros
    #[Route('/hero/{heroId}/competences', name: 'competence_list')]
    public function list(int $heroId, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);

        if (!$hero || $hero->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        $competences = $em->getRepository(Competence::class)->findBy(['hero' => $hero]);

        return $this->render('competence/list.html.twig', [
            'competences' => $competences,
            'hero' => $hero,
        ]);
    }

    // Créer une nouvelle compétence pour un héros
    #[Route('/hero/{heroId}/competence/new', name: 'competence_new')]
    public function new(int $heroId, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);

        if (!$hero || $hero->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        if ($request->isMethod('POST')) {
            $competence = new Competence();
            $competence->setName($request->request->get('name'))
                       ->setDescription($request->request->get('description'))
                       ->setEffet($request->request->get('effet'))
                       ->setOrigine($request->request->get('origine'))
                       ->setHero($hero);

            $em->persist($competence);
            $em->flush();

            return $this->redirectToRoute('competence_list', ['heroId' => $heroId]);
        }

        return $this->render('competence/new.html.twig', [
            'hero' => $hero,
        ]);
    }

    // Éditer une compétence existante
    #[Route('/hero/{heroId}/competence/edit/{id}', name: 'competence_edit')]
    public function edit(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $competence = $em->getRepository(Competence::class)->find($id);

        if (!$hero || $hero->getUser() !== $this->getUser() || !$competence || $competence->getHero()->getId() !== $heroId) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($request->isMethod('POST')) {
            $competence->setName($request->request->get('name'))
                       ->setDescription($request->request->get('description'))
                       ->setEffet($request->request->get('effet'))
                       ->setOrigine($request->request->get('origine'));

            $em->flush();

            return $this->redirectToRoute('competence_list', ['heroId' => $heroId]);
        }

        return $this->render('competence/new.html.twig', [
            'hero' => $hero,
            'competence' => $competence,
        ]);
    }

    // Supprimer une compétence
    #[Route('/hero/{heroId}/competence/delete/{id}', name: 'competence_delete', methods: ['POST'])]
    public function delete(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $competence = $em->getRepository(Competence::class)->find($id);

        if (!$hero || $hero->getUser() !== $this->getUser() || !$competence || $competence->getHero()->getId() !== $heroId) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isCsrfTokenValid('delete'.$competence->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('competence_list', ['heroId' => $heroId]);
        }

        $em->remove($competence);
        $em->flush();

        return $this->redirectToRoute('competence_list', ['heroId' => $heroId]);
    }
}
