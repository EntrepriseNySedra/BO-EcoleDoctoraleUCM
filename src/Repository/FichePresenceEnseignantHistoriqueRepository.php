<?php

namespace App\Repository;

use App\Entity\FichePresenceEnseignantHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FichePresenceEnseignantHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method FichePresenceEnseignantHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method FichePresenceEnseignantHistorique[]    findAll()
 * @method FichePresenceEnseignantHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FichePresenceEnseignantHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FichePresenceEnseignantHistorique::class);
    }

    // /**
    //  * @return FichePresenceEnseignantHistorique[] Returns an array of FichePresenceEnseignantHistorique objects
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
    public function findOneBySomeField($value): ?FichePresenceEnseignantHistorique
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
