<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Form\QuestType; // Je suppose que tu as un formulaire QuestType
use App\Repository\QuestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quest')]
class QuestController extends AbstractController
{
    #[Route('/', name: 'quest_index', methods: ['GET'])]
    public function index(QuestRepository $questRepository): Response
    {
        $quests = $questRepository->findAll();

        return $this->render('quest/index.html.twig', [
            'quests' => $quests,
        ]);
    }

    #[Route('/new', name: 'quest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quest = new Quest();
        $form = $this->createForm(QuestType::class, $quest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quest);
            $em->flush();

            $this->addFlash('success', 'La quête a bien été créée.');

            return $this->redirectToRoute('quest_index');
        }

        return $this->render('quest/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'quest_edit', methods: ['GET', 'POST'])]
    public function edit(Quest $quest, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestType::class, $quest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La quête a bien été modifiée.');

            return $this->redirectToRoute('quest_index');
        }

        return $this->render('quest/edit.html.twig', [
            'quest' => $quest,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'quest_delete', methods: ['POST'])]
    public function delete(Request $request, Quest $quest, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quest->getId(), $request->request->get('_token'))) {
            $em->remove($quest);
            $em->flush();

            $this->addFlash('success', 'La quête a bien été supprimée.');
        }

        return $this->redirectToRoute('quest_index');
    }
}
