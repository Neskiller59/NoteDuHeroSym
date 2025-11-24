<?php

namespace App\Controller;

use App\Entity\Hero;
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
    public function index(Request $request, HeroRepository $heroRepository, SessionInterface $session): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération de tous les héros de l'utilisateur
        $heroes = $heroRepository->findBy(['user' => $user]);

        // Récupération du héros sélectionné via query parameter
        $selectedHeroId = $request->query->get('hero_id');
        $selectedHero = null;

        if ($selectedHeroId) {
            $selectedHero = $heroRepository->findOneBy([
                'id' => $selectedHeroId,
                'user' => $user
            ]);

            // ⚡ Stockage du héros actif dans la session
            if ($selectedHero) {
                $session->set('active_hero_id', $selectedHero->getId());
            }
        } else {
            // Si aucun héros sélectionné, on prend le héros actif en session
            $activeHeroId = $session->get('active_hero_id');
            if ($activeHeroId) {
                $selectedHero = $heroRepository->findOneBy([
                    'id' => $activeHeroId,
                    'user' => $user
                ]);
            }
        }

        return $this->render('home/index.html.twig', [
            'heroes' => $heroes,
            'selectedHero' => $selectedHero,
        ]);
    }

    #[Route('/hero/delete/{id}', name: 'hero_delete', methods: ['POST'])]
    public function delete(Request $request, Hero $hero, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifie que le héros appartient à l'utilisateur
        if ($hero->getUser() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer ce héros.');
            return $this->redirectToRoute('app_home');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('delete'.$hero->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_home');
        }

        $em->remove($hero);
        $em->flush();

        // Si le héros supprimé était actif, on supprime la référence en session
        $activeHeroId = $session->get('active_hero_id');
        if ($activeHeroId === $hero->getId()) {
            $session->remove('active_hero_id');
        }

        $this->addFlash('success', 'Héros supprimé avec succès.');
        return $this->redirectToRoute('app_home');
    }
}
