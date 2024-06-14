<?php

namespace App\Repository;

use App\Entity\FichePresenceEnseignant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichePresenceEnseignant|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichePresenceEnseignant|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichePresenceEnseignant[]    findAll()
 * @method FichePresenceEnseignant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichePresenceEnseignantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichePresenceEnseignant::class);
    }

    // /**
    //  * @return FichePresenceEnseignant[] Returns an array of FichePresenceEnseignant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FichePresenceEnseignant
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
