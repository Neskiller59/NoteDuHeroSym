<?php

namespace App\Controller;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'inventory_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $items = $em->getRepository(Inventory::class)->findBy(['user' => $user]);

        return $this->render('game/inventory.html.twig', ['items' => $items]);
    }

    #[Route('/inventory/new', name: 'inventory_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');
            $additionalInfo = $request->request->get('additionalInfo');

            $item = new Inventory();
            $item->setName($name)
                ->setDescription($description)
                ->setAdditionalInfo($additionalInfo)
                ->setUser($user);

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('inventory_list');
        }

        return $this->render('game/newInventory.html.twig');
    }

    #[Route('/inventory/edit/{id}', name: 'inventory_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $item = $em->getRepository(Inventory::class)->find($id);

        if (!$item || $item->getUser() !== $user) {
            throw $this->createNotFoundException('Objet introuvable ou accès refusé.');
        }

        if ($request->isMethod('POST')) {
            $item->setName($request->request->get('name'));
            $item->setDescription($request->request->get('description'));
            $item->setAdditionalInfo($request->request->get('additionalInfo'));

            $em->flush();

            return $this->redirectToRoute('inventory_list');
        }

        return $this->render('game/newInventory.html.twig', ['item' => $item]);
    }

    #[Route('/inventory/delete/{id}', name: 'inventory_delete', methods:['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $item = $em->getRepository(Inventory::class)->find($id);

        if ($item && $item->getUser() === $user) {
            $em->remove($item);
            $em->flush();
        }

        return $this->redirectToRoute('inventory_list');
    }
}
