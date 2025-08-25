<?php

namespace App\Controller;

use App\Entity\Quest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewQuestController extends AbstractController
{
    #[Route('/quest/new', name: 'quest_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $quest = new Quest();
            $quest->setName($request->request->get('name'))
                  ->setObjectif($request->request->get('objectif'))
                  ->setInformation($request->request->get('information'))
                  ->setOrigine($request->request->get('origine'));

            $em->persist($quest);
            $em->flush();

            return $this->redirectToRoute('quest_index');
        }

        return $this->render('quest/new.html.twig');
    }
}
