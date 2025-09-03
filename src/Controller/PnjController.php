<?php

namespace App\Controller;

use App\Entity\Pnj;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PnjController extends AbstractController
{
    #[Route('/pnj', name: 'pnj_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $pnjs = $em->getRepository(Pnj::class)->findAll();

        return $this->render('pnj/list.html.twig', [
            'pnjs' => $pnjs,
        ]);
    }

    #[Route('/pnj/new', name: 'pnj_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $pnj = new Pnj();
            $pnj->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setInformation($request->request->get('information'))
                ->setLocalisation($request->request->get('localisation'))
                ->setPersonaliter($request->request->get('personaliter'))
                ->setCompetence($request->request->get('competence'));

            $em->persist($pnj);
            $em->flush();

            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig');
    }

    #[Route('/pnj/edit/{id}', name: 'pnj_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $pnj = $em->getRepository(Pnj::class)->find($id);

        if (!$pnj) {
            throw $this->createNotFoundException('PNJ introuvable.');
        }

        if ($request->isMethod('POST')) {
            $pnj->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setInformation($request->request->get('information'))
                ->setLocalisation($request->request->get('localisation'))
                ->setPersonaliter($request->request->get('personaliter'))
                ->setCompetence($request->request->get('competence'));

            $em->flush();

            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig', [
            'pnj' => $pnj,
        ]);
    }

    #[Route('/pnj/delete/{id}', name: 'pnj_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $pnj = $em->getRepository(Pnj::class)->find($id);

        if ($pnj) {
            $em->remove($pnj);
            $em->flush();
        }

        return $this->redirectToRoute('pnj_list');
    }
}
