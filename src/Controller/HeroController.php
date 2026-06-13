<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Form\HeroType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HeroController extends AbstractController
{
    // =========================
    // HERO LIST
    // =========================
    #[Route('/hero', name: 'hero_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $heroes = $em->getRepository(Hero::class)->findBy([
            'user' => $user
        ]);

        return $this->render('hero/index.html.twig', [
            'heroes' => $heroes
        ]);
    }

    // =========================
    // CREATE HERO
    // =========================
    #[Route('/hero/new', name: 'hero_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $hero = new Hero();
        $hero->setUser($user);

        $form = $this->createForm(HeroType::class, $hero);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // =========================
            // IMAGE UPLOAD
            // =========================

            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {

                $newFilename = uniqid() . '.jpg';

                $sourcePath = $photoFile->getPathname();

                $destinationPath =
                    $this->getParameter('kernel.project_dir')
                    . '/public/uploads/'
                    . $newFilename;

                $image = imagecreatefromstring(
                    file_get_contents($sourcePath)
                );

                if ($image) {

                    $width = imagesx($image);
                    $height = imagesy($image);

                    $size = min($width, $height);

                    $x = (int)(($width - $size) / 2);
                    $y = (int)(($height - $size) / 2);

                    $cropped = imagecrop($image, [
                        'x' => $x,
                        'y' => $y,
                        'width' => $size,
                        'height' => $size
                    ]);

                    if ($cropped) {

                        $resized = imagescale(
                            $cropped,
                            400,
                            400
                        );

                        imagejpeg(
                            $resized,
                            $destinationPath,
                            80
                        );

                        imagedestroy($cropped);
                        imagedestroy($resized);
                    }

                    imagedestroy($image);
                }

                $hero->setPhoto($newFilename);
            }

            $em->persist($hero);
            $em->flush();

            return $this->redirectToRoute('hero_index');
        }

        return $this->render('hero/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // =========================
    // SELECT HERO
    // =========================
    #[Route('/hero/select/{id}', name: 'hero_select')]
    public function select(
        Hero $hero,
        SessionInterface $session
    ): Response {

        $user = $this->getUser();

        if (!$user || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // =========================
        // SAVE ACTIVE HERO
        // =========================
        $session->set('active_hero_id', $hero->getId());

        // =========================
        // REDIRECT TO GAME
        // =========================
        return $this->redirectToRoute('inventory_list', [
            'heroId' => $hero->getId()
        ]);
    }

    // =========================
    // DELETE HERO
    // =========================
    #[Route('/hero/delete/{id}', name: 'hero_delete', methods: ['POST'])]
    public function delete(
        Hero $hero,
        EntityManagerInterface $em,
        Request $request,
        SessionInterface $session
    ): Response {

        $user = $this->getUser();

        if (!$user || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (
            $this->isCsrfTokenValid(
                'delete' . $hero->getId(),
                $request->request->get('_token')
            )
        ) {

            // =========================
            // REMOVE ACTIVE HERO
            // =========================
            if ($session->get('active_hero_id') == $hero->getId()) {
                $session->remove('active_hero_id');
            }

            $em->remove($hero);
            $em->flush();
        }

        return $this->redirectToRoute('hero_index');
    }
}