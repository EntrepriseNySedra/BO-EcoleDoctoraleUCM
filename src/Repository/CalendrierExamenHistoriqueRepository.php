<?php

namespace App\Repository;

use App\Entity\CalendrierExamenHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierExamenHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierExamenHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierExamenHistorique[]    findAll()
 * @method CalendrierExamenHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierExamenHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierExamenHistorique::class);
    }

    // /**
    //  * @return CalendrierExamenHistorique[] Returns an array of CalendrierExamenHistorique objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CalendrierExamenHistorique
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
