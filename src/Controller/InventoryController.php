<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InventoryController extends AbstractController
{
    #[Route('/hero/{heroId}/inventory', name: 'inventory_list')]
    public function list(int $heroId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $hero = $em->getRepository(Hero::class)->find($heroId);
        if (!$hero || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        $items = $em->getRepository(Inventory::class)->findBy(['hero' => $hero]);

        return $this->render('game/inventory.html.twig', [
            'items' => $items,
            'hero' => $hero
        ]);
    }

    #[Route('/hero/{heroId}/inventory/new', name: 'inventory_new')]
    public function new(int $heroId, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $hero = $em->getRepository(Hero::class)->find($heroId);
        if (!$hero || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        if ($request->isMethod('POST')) {
            $item = new Inventory();
            $item->setName($request->request->get('name'))
                 ->setDescription($request->request->get('description'))
                 ->setAdditionalInfo($request->request->get('additionalInfo'))
                 ->setHero($hero);

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('inventory_list', ['heroId' => $heroId]);
        }

        return $this->render('game/newInventory.html.twig', [
            'hero' => $hero
        ]);
    }

    #[Route('/inventory/{id}/edit', name: 'inventory_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Inventory::class)->find($id);
        $user = $this->getUser();

        if (!$item || $item->getHero()->getUser() !== $user) {
            throw $this->createNotFoundException('Objet introuvable ou accès refusé.');
        }

        if ($request->isMethod('POST')) {
            $item->setName($request->request->get('name'))
                 ->setDescription($request->request->get('description'))
                 ->setAdditionalInfo($request->request->get('additionalInfo'));

            $em->flush();

            return $this->redirectToRoute('inventory_list', [
                'heroId' => $item->getHero()->getId()
            ]);
        }

        return $this->render('game/newInventory.html.twig', [
            'item' => $item,
            'hero' => $item->getHero()
        ]);
    }

    #[Route('/inventory/{id}/delete', name: 'inventory_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Inventory::class)->find($id);
        $user = $this->getUser();

        if (!$item || $item->getHero()->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $heroId = $item->getHero()->getId();
            $em->remove($item);
            $em->flush();

            return $this->redirectToRoute('inventory_list', ['heroId' => $heroId]);
        }

        return $this->redirectToRoute('inventory_list', [
            'heroId' => $item->getHero()->getId()
        ]);
    }

    // =========================
    // Nouvelle route : update gold
    // =========================
    #[Route('/hero/{heroId}/inventory/update-gold', name: 'inventory_update_gold', methods: ['POST'])]
    public function updateGold(int $heroId, Request $request, EntityManagerInterface $em): Response
    {
        $hero = $em->getRepository(Hero::class)->find($heroId);
        if (!$hero || $hero->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Héros introuvable ou non autorisé.');
        }

        $amount = (int) $request->request->get('amount', 0);
        $hero->setGold(max(0, $hero->getGold() + $amount)); // empêche l'or négatif

        $em->flush();

        return $this->redirectToRoute('inventory_list', ['heroId' => $heroId]);
    }
}
