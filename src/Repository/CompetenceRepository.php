<?php

namespace App\Repository;

use App\Entity\Competence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competence>
 */
class CompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competence::class);
    }

    /**
     * Trouve les compétences actives
     */
    public function findActiveCompetences(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.actif = :actif')
            ->setParameter('actif', 1)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}