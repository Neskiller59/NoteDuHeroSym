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
            'user' => $user,
        ]);

        return $this->render('hero/index.html.twig', [
            'heroes' => $heroes,
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
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $destinationDirectory =
                    $this->getParameter('kernel.project_dir')
                    . '/public/uploads/';

                if (!is_dir($destinationDirectory)) {
                    mkdir($destinationDirectory, 0755, true);
                }

                $newFilename = bin2hex(random_bytes(16)) . '.jpg';

                $imageData = file_get_contents($photoFile->getPathname());
                $image = $imageData !== false
                    ? imagecreatefromstring($imageData)
                    : false;

                if ($image !== false) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $size = min($width, $height);

                    $cropped = imagecrop($image, [
                        'x' => (int) (($width - $size) / 2),
                        'y' => (int) (($height - $size) / 2),
                        'width' => $size,
                        'height' => $size,
                    ]);

                    if ($cropped !== false) {
                        $resized = imagescale($cropped, 400, 400);

                        if ($resized !== false) {
                            $saved = imagejpeg(
                                $resized,
                                $destinationDirectory . $newFilename,
                                80
                            );

                            imagedestroy($resized);

                            if ($saved) {
                                $hero->setPhoto($newFilename);
                            }
                        }

                        imagedestroy($cropped);
                    }

                    imagedestroy($image);
                }
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

        $session->set('active_hero_id', $hero->getId());

        return $this->redirectToRoute('inventory_list', [
            'heroId' => $hero->getId(),
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

        if ($this->isCsrfTokenValid(
            'delete' . $hero->getId(),
            $request->request->get('_token')
        )) {
            if ($session->get('active_hero_id') == $hero->getId()) {
                $session->remove('active_hero_id');
            }

            $em->remove($hero);
            $em->flush();
        }

        return $this->redirectToRoute('hero_index');
    }

    // =========================
    // EDIT HERO
    // =========================
    #[Route('/hero/{id}/edit', name: 'hero_edit', methods: ['GET', 'POST'])]
    public function edit(
        Hero $hero,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // Vérifier que le joueur possède ce héros
        if (!$user || $hero->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Mémoriser l'ancienne photo avant toute modification
        $oldPhoto = $hero->getPhoto();

        $form = $this->createForm(HeroType::class, $hero);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            $removePhoto = $form->get('removePhoto')->getData();

            $destinationDirectory =
                $this->getParameter('kernel.project_dir')
                . '/public/uploads/';

            // Créer le dossier d'images si nécessaire
            if (!is_dir($destinationDirectory)) {
                mkdir($destinationDirectory, 0755, true);
            }

            // =========================
            // NOUVELLE PHOTO
            // =========================
            if ($photoFile) {
                $newFilename = bin2hex(random_bytes(16)) . '.jpg';

                $imageData = file_get_contents($photoFile->getPathname());
                $image = $imageData !== false
                    ? imagecreatefromstring($imageData)
                    : false;

                if ($image === false) {
                    $this->addFlash(
                        'danger',
                        'Impossible de lire cette image. Choisis un autre fichier.'
                    );

                    return $this->render('hero/edit.html.twig', [
                        'form' => $form->createView(),
                        'hero' => $hero,
                    ]);
                }

                $width = imagesx($image);
                $height = imagesy($image);
                $size = min($width, $height);

                // Recadrer l'image au format carré
                $cropped = imagecrop($image, [
                    'x' => (int) (($width - $size) / 2),
                    'y' => (int) (($height - $size) / 2),
                    'width' => $size,
                    'height' => $size,
                ]);

                if ($cropped === false) {
                    imagedestroy($image);

                    $this->addFlash(
                        'danger',
                        'Impossible de recadrer cette image.'
                    );

                    return $this->render('hero/edit.html.twig', [
                        'form' => $form->createView(),
                        'hero' => $hero,
                    ]);
                }

                // Redimensionner en 400 x 400 pixels
                $resized = imagescale($cropped, 400, 400);

                if ($resized === false) {
                    imagedestroy($cropped);
                    imagedestroy($image);

                    $this->addFlash(
                        'danger',
                        'Impossible de redimensionner cette image.'
                    );

                    return $this->render('hero/edit.html.twig', [
                        'form' => $form->createView(),
                        'hero' => $hero,
                    ]);
                }

                // Enregistrer la nouvelle photo
                $saved = imagejpeg(
                    $resized,
                    $destinationDirectory . $newFilename,
                    80
                );

                imagedestroy($resized);
                imagedestroy($cropped);
                imagedestroy($image);

                if (!$saved) {
                    $this->addFlash(
                        'danger',
                        'Impossible d’enregistrer cette image.'
                    );

                    return $this->render('hero/edit.html.twig', [
                        'form' => $form->createView(),
                        'hero' => $hero,
                    ]);
                }

                // La nouvelle photo remplace l'ancienne
                $hero->setPhoto($newFilename);
            }

            // =========================
            // SUPPRIMER LA PHOTO
            // =========================
            elseif ($removePhoto) {
                // L'entité ne possède plus de photo personnalisée
                $hero->setPhoto(null);
            }

            // Enregistrer les modifications du héros
            $em->flush();

            // Supprimer l'ancien fichier uniquement après l'enregistrement
            $photoChanged = $photoFile || $removePhoto;

            if (
                $photoChanged
                && $oldPhoto
                && $oldPhoto !== $hero->getPhoto()
            ) {
                $oldPhotoPath = $destinationDirectory . $oldPhoto;

                if (is_file($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $this->addFlash(
                'success',
                'Ton héros a été modifié.'
            );

            return $this->redirectToRoute('hero_index');
        }

        return $this->render('hero/edit.html.twig', [
            'form' => $form->createView(),
            'hero' => $hero,
        ]);
    }
}