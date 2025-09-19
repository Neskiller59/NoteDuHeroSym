<?php

namespace App\Controller;

use App\Entity\Competence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CompetenceController extends AbstractController
{
    #[Route('/competence', name: 'competence_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $competences = $em->getRepository(Competence::class)->findBy(['user' => $user]);

        return $this->render('competence/list.html.twig', [
            'competences' => $competences,
        ]);
    }

    #[Route('/competence/new', name: 'competence_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $competence = new Competence();
            $competence->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setEffet($request->request->get('effet'))
                ->setOrigine($request->request->get('origine'))
                ->setUser($this->getUser()); // 🔒 On associe l'utilisateur connecté

            $em->persist($competence);
            $em->flush();

            return $this->redirectToRoute('competence_list');
        }

        return $this->render('competence/new.html.twig');
    }

    #[Route('/competence/edit/{id}', name: 'competence_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $competence = $em->getRepository(Competence::class)->find($id);

        if (!$competence) {
            throw $this->createNotFoundException('Compétence introuvable.');
        }

        // 🔒 Sécurité : un utilisateur ne peut éditer que ses propres compétences
        if ($competence->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette compétence.');
        }

        if ($request->isMethod('POST')) {
            $competence->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setEffet($request->request->get('effet'))
                ->setOrigine($request->request->get('origine'));

            $em->flush();

            return $this->redirectToRoute('competence_list');
        }

        return $this->render('competence/new.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/competence/delete/{id}', name: 'competence_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $competence = $em->getRepository(Competence::class)->find($id);

        if ($competence && $competence->getUser() === $this->getUser()) {
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirectToRoute('competence_list');
    }
}
