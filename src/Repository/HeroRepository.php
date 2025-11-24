<?php

namespace App\Repository;

use App\Entity\Hero;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hero>
 */
class HeroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hero::class);
    }

    /**
     * Sauvegarde un personnage (crée ou met à jour)
     */
    public function save(Hero $Hero, bool $flush = false): void
    {
        $this->_em->persist($Hero);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Supprime un personnage
     */
    public function remove(Hero $Hero, bool $flush = false): void
    {
        $this->_em->remove($Hero);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Trouve tous les personnages appartenant à un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le personnage principal d’un utilisateur (si tu en définis un)
     */
    public function findMainHero(User $user): ?Hero
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.isMain = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
