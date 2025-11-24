<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')] // Toutes les actions nécessitent une connexion
class QuestController extends AbstractController
{
    // --- Liste des quêtes d'un héros ---
    #[Route('/hero/{heroId}/quests', name: 'quest_index')]
    public function index(int $heroId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $hero = $em->getRepository(Hero::class)->find($heroId);

        if (!$hero || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        $quests = $em->getRepository(Quest::class)->findBy(['hero' => $hero]);

        return $this->render('quest/index.html.twig', [
            'hero' => $hero,
            'quests' => $quests,
        ]);
    }

    // --- Création d'une nouvelle quête ---
    #[Route('/hero/{heroId}/quest/new', name: 'quest_new')]
    public function new(int $heroId, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $hero = $em->getRepository(Hero::class)->find($heroId);

        if (!$hero || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        if ($request->isMethod('POST')) {
            $quest = new Quest();
            $quest->setName($request->request->get('name'));
            $quest->setObjectif($request->request->get('objectif') ?? ''); // Valeur par défaut
            $quest->setInformation($request->request->get('information') ?? '');
            $quest->setOrigine($request->request->get('origine') ?? '');
            $quest->setHero($hero);

            $em->persist($quest);
            $em->flush();

            return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
        }

        return $this->render('quest/new.html.twig', [
            'hero' => $hero,
        ]);
    }

    // --- Édition d'une quête ---
    #[Route('/hero/{heroId}/quest/edit/{id}', name: 'quest_edit')]
    public function edit(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $quest = $em->getRepository(Quest::class)->find($id);

        if (!$hero || $hero->getUser() !== $user || !$quest || $quest->getHero()->getId() !== $heroId) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($request->isMethod('POST')) {
            $quest->setName($request->request->get('name'));
            $quest->setObjectif($request->request->get('objectif') ?? '');
            $quest->setInformation($request->request->get('information') ?? '');
            $quest->setOrigine($request->request->get('origine') ?? '');

            $em->flush();

            return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
        }

        return $this->render('quest/edit.html.twig', [
            'hero' => $hero,
            'quest' => $quest,
        ]);
    }

    // --- Suppression d'une quête ---
    #[Route('/hero/{heroId}/quest/delete/{id}', name: 'quest_delete', methods: ['POST'])]
    public function delete(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $quest = $em->getRepository(Quest::class)->find($id);

        if (!$hero || $hero->getUser() !== $user || !$quest || $quest->getHero()->getId() !== $heroId) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isCsrfTokenValid('delete'.$quest->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
        }

        $em->remove($quest);
        $em->flush();

        return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
    }
}
