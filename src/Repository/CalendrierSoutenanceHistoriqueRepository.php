<?php

namespace App\Repository;

use App\Entity\CalendrierSoutenanceHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierSoutenanceHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierSoutenanceHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierSoutenanceHistorique[]    findAll()
 * @method CalendrierSoutenanceHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierSoutenanceHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierSoutenanceHistorique::class);
    }

    // /**
    //  * @return CalendrierSoutenanceHistorique[] Returns an array of CalendrierSoutenanceHistorique objects
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
    public function findOneBySomeField($value): ?CalendrierSoutenanceHistorique
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
