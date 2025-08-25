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
        // ⚠️ Vérifie que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $objectif = $request->request->get('objectif');
            $information = $request->request->get('information');
            $origine = $request->request->get('origine');

            $quest = new Quest();
            $quest->setName($name)
                  ->setObjectif($objectif)
                  ->setInformation($information)
                  ->setOrigine($origine)
                  ->setUser($user); // ✅ Association automatique au user connecté

            $em->persist($quest);
            $em->flush();

            $this->addFlash('success', 'Nouvelle quête ajoutée avec succès !');

            return $this->redirectToRoute('quest_index');
        }

        return $this->render('quest/new.html.twig');
    }
}
