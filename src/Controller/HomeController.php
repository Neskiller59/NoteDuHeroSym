<?php

namespace App\Controller;

use App\Repository\HeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        HeroRepository $heroRepository,
        SessionInterface $session
    ): Response {

        $user = $this->getUser();

        // 🔒 Non connecté → login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /**
         * 🎮 RPG FLOW LOGIC
         *
         * - Si aucun héros actif → on va sur /hero
         * - Sinon on y va quand même (hub unique)
         */

        $activeHeroId = $session->get('active_hero_id');

        if (!$activeHeroId) {
            return $this->redirectToRoute('hero_index');
        }

        // ⚠️ sécurité : vérifier que le héros existe toujours
        $hero = $heroRepository->findOneBy([
            'id' => $activeHeroId,
            'user' => $user
        ]);

        if (!$hero) {
            $session->remove('active_hero_id');
            return $this->redirectToRoute('hero_index');
        }

        // 🎯 OPTION : tu peux rediriger vers inventaire directement
        // ou laisser hero_index comme hub RPG

        return $this->redirectToRoute('hero_index');
    }

    // (optionnel mais recommandé si tu veux garder /home en debug)
    #[Route('/home/debug', name: 'app_home_debug')]
    public function debug(): Response
    {
        return $this->render('home/debug.html.twig');
    }
}