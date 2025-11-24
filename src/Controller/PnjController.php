<?php

namespace App\Controller;

use App\Entity\Pnj;
use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PnjController extends AbstractController
{
    // Liste des PNJs pour le héros actif
    #[Route('/pnjs', name: 'pnj_list')]
    public function list(EntityManagerInterface $em, SessionInterface $session): Response
    {
        $user = $this->getUser();

        // Récupération du héros actif en session
        $activeHeroId = $session->get('active_hero_id');
        if (!$activeHeroId) {
            $this->addFlash('warning', 'Veuillez sélectionner un héros pour voir ses PNJs.');
            return $this->redirectToRoute('app_home');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);
        if (!$hero || $hero->getUser() !== $user) {
            $this->addFlash('error', 'Héros introuvable ou non autorisé.');
            return $this->redirectToRoute('app_home');
        }

        $pnjs = $em->getRepository(Pnj::class)->findBy(['hero' => $hero]);

        return $this->render('pnj/list.html.twig', [
            'hero' => $hero,
            'pnjs' => $pnjs,
        ]);
    }

    // Créer un nouveau PNJ pour le héros actif
    #[Route('/pnj/new', name: 'pnj_new')]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $user = $this->getUser();

        $activeHeroId = $session->get('active_hero_id');
        if (!$activeHeroId) {
            $this->addFlash('warning', 'Veuillez sélectionner un héros avant de créer un PNJ.');
            return $this->redirectToRoute('app_home');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);
        if (!$hero || $hero->getUser() !== $user) {
            $this->addFlash('error', 'Héros introuvable ou non autorisé.');
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $pnj = new Pnj();
            $pnj->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setInformation($request->request->get('information'))
                ->setLocalisation($request->request->get('localisation'))
                ->setPersonnalite($request->request->get('personnalite'))
                ->setCompetence($request->request->get('competence'))
                ->setHero($hero);

            $em->persist($pnj);
            $em->flush();

            $this->addFlash('success', 'PNJ créé avec succès.');
            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig', [
            'hero' => $hero,
        ]);
    }

    // Éditer un PNJ
    #[Route('/pnj/edit/{id}', name: 'pnj_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $activeHeroId = $session->get('active_hero_id');
        if (!$activeHeroId) {
            $this->addFlash('warning', 'Veuillez sélectionner un héros avant de modifier un PNJ.');
            return $this->redirectToRoute('app_home');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);
        $pnj = $em->getRepository(Pnj::class)->find($id);

        if (!$hero || $hero->getUser() !== $user || !$pnj || !$pnj->getHero() || $pnj->getHero()->getId() !== $activeHeroId) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('pnj_list');
        }

        if ($request->isMethod('POST')) {
            $pnj->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setInformation($request->request->get('information'))
                ->setLocalisation($request->request->get('localisation'))
                ->setPersonnalite($request->request->get('personnalite'))
                ->setCompetence($request->request->get('competence'));

            $em->flush();

            $this->addFlash('success', 'PNJ mis à jour.');
            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig', [
            'hero' => $hero,
            'pnj' => $pnj,
        ]);
    }

    // Supprimer un PNJ
    #[Route('/pnj/delete/{id}', name: 'pnj_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $activeHeroId = $session->get('active_hero_id');
        if (!$activeHeroId) {
            $this->addFlash('warning', 'Veuillez sélectionner un héros avant de supprimer un PNJ.');
            return $this->redirectToRoute('app_home');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);
        $pnj = $em->getRepository(Pnj::class)->find($id);

        if (!$hero || $hero->getUser() !== $user || !$pnj || !$pnj->getHero() || $pnj->getHero()->getId() !== $activeHeroId) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('pnj_list');
        }

        if (!$this->isCsrfTokenValid('delete'.$pnj->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('pnj_list');
        }

        $em->remove($pnj);
        $em->flush();

        $this->addFlash('success', 'PNJ supprimé.');
        return $this->redirectToRoute('pnj_list');
    }
}
