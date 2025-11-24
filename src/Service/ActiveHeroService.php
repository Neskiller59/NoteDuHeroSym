<?php

namespace App\Service;

use App\Entity\Hero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ActiveHeroService
{
    private SessionInterface $session;
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(SessionInterface $session, EntityManagerInterface $em, Security $security)
    {
        $this->session = $session;
        $this->em = $em;
        $this->security = $security;
    }

    public function setActiveHero(Hero $hero): void
    {
        $this->session->set('active_hero_id', $hero->getId());
    }

    public function getActiveHero(): ?Hero
    {
        $heroId = $this->session->get('active_hero_id');
        $user = $this->security->getUser();

        if (!$heroId || !$user) {
            return null;
        }

        $hero = $this->em->getRepository(Hero::class)->find($heroId);

        if (!$hero || $hero->getUser() !== $user) {
            $this->session->remove('active_hero_id');
            return null;
        }

        return $hero;
    }
}
