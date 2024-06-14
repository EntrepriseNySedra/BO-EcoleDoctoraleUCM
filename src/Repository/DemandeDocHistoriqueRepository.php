<?php

namespace App\Repository;

use App\Entity\DemandeDocHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DemandeDocHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeDocHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeDocHistorique[]    findAll()
 * @method DemandeDocHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeDocHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDocHistorique::class);
    }

    // /**
    //  * @return DemandeDocHistorique[] Returns an array of DemandeDocHistorique objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DemandeDocHistorique
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
