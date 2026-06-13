<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class InventoryController extends AbstractController
{
    // =========================
    // INVENTORY LIST
    // =========================

    #[Route('/inventory', name: 'inventory_list')]
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
        // INVENTORY
        // =========================
        $items = $em->getRepository(Inventory::class)->findBy([
            'hero' => $hero
        ]);

        return $this->render('game/inventory.html.twig', [
            'hero' => $hero,
            'items' => $items
        ]);
    }

    // =========================
    // NEW ITEM
    // =========================

    #[Route('/inventory/new', name: 'inventory_new')]
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
        // CREATE ITEM
        // =========================
        if ($request->isMethod('POST')) {

            $item = new Inventory();

            $item->setName(
                $request->request->get('name')
            );

            $item->setDescription(
                $request->request->get('description')
            );

            $item->setAdditionalInfo(
                $request->request->get('additionalInfo')
            );

            $item->setHero($hero);

            $em->persist($item);
            $em->flush();

            $this->addFlash(
                'success',
                'Objet ajouté à l\'inventaire.'
            );

            return $this->redirectToRoute('inventory_list');
        }

        return $this->render('game/newInventory.html.twig', [
            'hero' => $hero
        ]);
    }

    // =========================
    // EDIT ITEM
    // =========================

    #[Route('/inventory/{id}/edit', name: 'inventory_edit')]
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

        $item = $em->getRepository(Inventory::class)->find($id);

        // =========================
        // SECURITY
        // =========================
        if (
            !$item ||
            !$item->getHero() ||
            $item->getHero()->getUser() !== $user ||
            $item->getHero()->getId() !== $activeHeroId
        ) {

            $this->addFlash(
                'error',
                'Accès refusé.'
            );

            return $this->redirectToRoute('inventory_list');
        }

        // =========================
        // UPDATE ITEM
        // =========================
        if ($request->isMethod('POST')) {

            $item->setName(
                $request->request->get('name')
            );

            $item->setDescription(
                $request->request->get('description')
            );

            $item->setAdditionalInfo(
                $request->request->get('additionalInfo')
            );

            $em->flush();

            $this->addFlash(
                'success',
                'Objet modifié.'
            );

            return $this->redirectToRoute('inventory_list');
        }

        return $this->render('game/newInventory.html.twig', [
            'item' => $item,
            'hero' => $item->getHero()
        ]);
    }

    // =========================
    // DELETE ITEM
    // =========================

    #[Route('/inventory/{id}/delete', name: 'inventory_delete', methods: ['POST'])]
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

        $item = $em->getRepository(Inventory::class)->find($id);

        // =========================
        // SECURITY
        // =========================
        if (
            !$item ||
            !$item->getHero() ||
            $item->getHero()->getUser() !== $user ||
            $item->getHero()->getId() !== $activeHeroId
        ) {

            $this->addFlash(
                'error',
                'Accès refusé.'
            );

            return $this->redirectToRoute('inventory_list');
        }

        // =========================
        // CSRF
        // =========================
        if (
            !$this->isCsrfTokenValid(
                'delete'.$item->getId(),
                $request->request->get('_token')
            )
        ) {

            $this->addFlash(
                'error',
                'Token invalide.'
            );

            return $this->redirectToRoute('inventory_list');
        }

        $em->remove($item);
        $em->flush();

        $this->addFlash(
            'success',
            'Objet supprimé.'
        );

        return $this->redirectToRoute('inventory_list');
    }

    // =========================
    // UPDATE GOLD (AJAX)
    // Corrections :
    //   1. active_hero_id  (au lieu de hero_id)
    //   2. lecture du champ 'gold' envoyé par le JS
    //   3. retour JSON pour feedback AJAX
    // =========================

    #[Route('/inventory/update-gold', name: 'inventory_update_gold', methods: ['POST'])]
    public function updateGold(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié.'], 401);
        }

        // ✅ FIX 1 — même clé de session que tout le reste du controller
        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {
            return new JsonResponse(['error' => 'Aucun héros actif.'], 400);
        }

        $hero = $em->getRepository(Hero::class)->find($activeHeroId);

        // ✅ FIX 2 — sécurité : le héros appartient bien à l'utilisateur connecté
        if (!$hero || $hero->getUser() !== $user) {
            $session->remove('active_hero_id');
            return new JsonResponse(['error' => 'Héros introuvable.'], 403);
        }

        // ✅ FIX 3 — le champ envoyé par le JS s'appelle 'gold', pas 'amount'
        $gold = (int) $request->request->get('gold', $hero->getGold());

        if ($gold < 0) {
            $gold = 0;
        }

        $hero->setGold($gold);
        $em->flush();

        // ✅ Retour JSON avec la valeur sauvegardée pour confirmer à l'UI
        return new JsonResponse(['gold' => $hero->getGold()]);
    }
}