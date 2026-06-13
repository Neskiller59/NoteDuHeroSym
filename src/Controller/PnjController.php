<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Entity\Pnj;
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
    // =========================
    // LISTE DES PNJ
    // =========================

    #[Route('/pnj', name: 'pnj_list')]
    public function list(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        // =========================
        // HERO ACTIF
        // =========================
        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {

            $this->addFlash(
                'warning',
                'Veuillez sélectionner un héros.'
            );

            return $this->redirectToRoute('hero_index');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);

        // =========================
        // SECURITY HERO OWNER
        // =========================
        if (
            !$hero ||
            $hero->getUser() !== $user
        ) {

            $session->remove('active_hero_id');

            $this->addFlash(
                'error',
                'Héros introuvable.'
            );

            return $this->redirectToRoute('hero_index');
        }

        // =========================
        // PNJ DU HERO
        // =========================
        $pnjs = $em->getRepository(Pnj::class)->findBy([
            'hero' => $hero
        ]);

        return $this->render('pnj/list.html.twig', [
            'hero' => $hero,
            'pnjs' => $pnjs
        ]);
    }

    // =========================
    // CREATE PNJ
    // =========================

    #[Route('/pnj/new', name: 'pnj_new')]
    public function new(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {

            $this->addFlash(
                'warning',
                'Veuillez sélectionner un héros.'
            );

            return $this->redirectToRoute('hero_index');
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);

        if (
            !$hero ||
            $hero->getUser() !== $user
        ) {

            $session->remove('active_hero_id');

            return $this->redirectToRoute('hero_index');
        }

        // =========================
        // CREATE
        // =========================
        if ($request->isMethod('POST')) {

            $pnj = new Pnj();

            $pnj->setName(
                $request->request->get('name')
            );

            $pnj->setDescription(
                $request->request->get('description')
            );

            $pnj->setInformation(
                $request->request->get('information')
            );

            $pnj->setLocalisation(
                $request->request->get('localisation')
            );

            $pnj->setPersonnalite(
                $request->request->get('personnalite')
            );

            $pnj->setCompetence(
                $request->request->get('competence')
            );

            $pnj->setHero($hero);

            $em->persist($pnj);
            $em->flush();

            $this->addFlash(
                'success',
                'PNJ ajouté.'
            );

            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig', [
            'hero' => $hero
        ]);
    }

    // =========================
    // EDIT PNJ
    // =========================

    #[Route('/pnj/{id}/edit', name: 'pnj_edit')]
    public function edit(
        int $id,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {

            return $this->redirectToRoute('hero_index');
        }

        $pnj = $em->getRepository(Pnj::class)->find($id);

        // =========================
        // SECURITY
        // =========================
        if (
            !$pnj ||
            !$pnj->getHero() ||
            $pnj->getHero()->getUser() !== $user ||
            $pnj->getHero()->getId() !== $activeHeroId
        ) {

            $this->addFlash(
                'error',
                'Accès refusé.'
            );

            return $this->redirectToRoute('pnj_list');
        }

        // =========================
        // UPDATE
        // =========================
        if ($request->isMethod('POST')) {

            $pnj->setName(
                $request->request->get('name')
            );

            $pnj->setDescription(
                $request->request->get('description')
            );

            $pnj->setInformation(
                $request->request->get('information')
            );

            $pnj->setLocalisation(
                $request->request->get('localisation')
            );

            $pnj->setPersonnalite(
                $request->request->get('personnalite')
            );

            $pnj->setCompetence(
                $request->request->get('competence')
            );

            $em->flush();

            $this->addFlash(
                'success',
                'PNJ modifié.'
            );

            return $this->redirectToRoute('pnj_list');
        }

        return $this->render('pnj/new.html.twig', [
            'hero' => $pnj->getHero(),
            'pnj' => $pnj
        ]);
    }

    // =========================
    // DELETE PNJ
    // =========================

    #[Route('/pnj/{id}/delete', name: 'pnj_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {

            return $this->redirectToRoute('hero_index');
        }

        $pnj = $em->getRepository(Pnj::class)->find($id);

        // =========================
        // SECURITY
        // =========================
        if (
            !$pnj ||
            !$pnj->getHero() ||
            $pnj->getHero()->getUser() !== $user ||
            $pnj->getHero()->getId() !== $activeHeroId
        ) {

            $this->addFlash(
                'error',
                'Accès refusé.'
            );

            return $this->redirectToRoute('pnj_list');
        }

        // =========================
        // CSRF
        // =========================
        if (
            !$this->isCsrfTokenValid(
                'delete'.$pnj->getId(),
                $request->request->get('_token')
            )
        ) {

            $this->addFlash(
                'error',
                'Token invalide.'
            );

            return $this->redirectToRoute('pnj_list');
        }

        $em->remove($pnj);
        $em->flush();

        $this->addFlash(
            'success',
            'PNJ supprimé.'
        );

        return $this->redirectToRoute('pnj_list');
    }
}