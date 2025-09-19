<?php

namespace App\Repository;

use App\Entity\Competence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competence>
 *
 * @method Competence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competence[]    findAll()
 * @method Competence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competence::class);
    }

    /**
     * Exemple : récupérer toutes les compétences d’une origine donnée
     */
    public function findByOrigine(string $origine): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.origine = :origine')
            ->setParameter('origine', $origine)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple : chercher une compétence par mot-clé (dans le nom ou la description)
     */
    public function search(string $keyword): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :kw OR c.description LIKE :kw')
            ->setParameter('kw', '%' . $keyword . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
