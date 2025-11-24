<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hero/{heroId}/quest')]
class NewQuestController extends AbstractController
{
    // Liste des quêtes d’un héros
    #[Route('/', name: 'quest_index')]
    public function index(int $heroId, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        if (!$hero || $hero->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        $quests = $em->getRepository(Quest::class)->findBy(['user' => $hero->getUser()]);

        return $this->render('quest/index.html.twig', [
            'hero' => $hero,
            'quests' => $quests,
        ]);
    }

    // Création d’une nouvelle quête
    #[Route('/new', name: 'quest_new')]
    public function new(int $heroId, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        if (!$hero || $hero->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        if ($request->isMethod('POST')) {
            $quest = new Quest();
            $quest->setName($request->request->get('name'))
                  ->setObjectif($request->request->get('objectif'))
                  ->setInformation($request->request->get('information'))
                  ->setOrigine($request->request->get('origine'))
                  ->setUser($hero->getUser());

            $em->persist($quest);
            $em->flush();

            $this->addFlash('success', 'Nouvelle quête ajoutée avec succès !');

            return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
        }

        return $this->render('quest/new.html.twig', [
            'hero' => $hero
        ]);
    }

    // Modification d’une quête
    #[Route('/{id}/edit', name: 'quest_edit')]
    public function edit(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $quest = $em->getRepository(Quest::class)->find($id);

        if (!$hero || $hero->getUser() !== $this->getUser() || !$quest || $quest->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros ou quête introuvable ou non autorisé.');
        }

        if ($request->isMethod('POST')) {
            $quest->setName($request->request->get('name'))
                  ->setObjectif($request->request->get('objectif'))
                  ->setInformation($request->request->get('information'))
                  ->setOrigine($request->request->get('origine'));

            $em->flush();
            $this->addFlash('success', 'Quête modifiée avec succès !');

            return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
        }

        return $this->render('quest/edit.html.twig', [
            'hero' => $hero,
            'quest' => $quest
        ]);
    }

    // Suppression d’une quête
    #[Route('/{id}/delete', name: 'quest_delete', methods: ['POST'])]
    public function delete(int $heroId, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        $quest = $em->getRepository(Quest::class)->find($id);

        if (!$hero || $hero->getUser() !== $this->getUser() || !$quest || $quest->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros ou quête introuvable ou non autorisé.');
        }

        if ($this->isCsrfTokenValid('delete'.$quest->getId(), $request->request->get('_token'))) {
            $em->remove($quest);
            $em->flush();
            $this->addFlash('success', 'Quête supprimée avec succès !');
        }

        return $this->redirectToRoute('quest_index', ['heroId' => $heroId]);
    }
}
