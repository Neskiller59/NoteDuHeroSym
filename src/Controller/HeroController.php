<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Form\HeroType; // formulaire Symfony pour Hero
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HeroController extends AbstractController
{
    #[Route('/hero/new', name: 'hero_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $hero = new Hero();
        $hero->setUser($user);

        $form = $this->createForm(HeroType::class, $hero);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($hero);
            $em->flush();

            return $this->redirectToRoute('app_home', ['hero_id' => $hero->getId()]);
        }

        return $this->render('hero/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
