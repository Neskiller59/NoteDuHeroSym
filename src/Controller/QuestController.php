<?php

namespace App\Controller;

use App\Entity\Quest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestController extends AbstractController
{
    #[Route('/quest/', name: 'quest_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // Accès réservé aux utilisateurs connectés
        $this->denyAccessUnlessGranted('ROLE_USER');

        $quests = $em->getRepository(Quest::class)->findAll();
        return $this->render('quest/index.html.twig', ['quests' => $quests]);
    }

    #[Route('/quest/{id}/edit', name: 'quest_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $quest = $em->getRepository(Quest::class)->find($id);
        if (!$quest) {
            throw $this->createNotFoundException('Quête introuvable');
        }

        if ($request->isMethod('POST')) {
            $quest->setName($request->request->get('name'))
                  ->setObjectif($request->request->get('objectif'))
                  ->setInformation($request->request->get('information'))
                  ->setOrigine($request->request->get('origine'));

            $em->flush();
            return $this->redirectToRoute('quest_index');
        }

        return $this->render('quest/edit.html.twig', ['quest' => $quest]);
    }

    #[Route('/quest/{id}', name: 'quest_delete', methods:['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $quest = $em->getRepository(Quest::class)->find($id);
        if ($quest) {
            $em->remove($quest);
            $em->flush();
        }

        return $this->redirectToRoute('quest_index');
    }
}
