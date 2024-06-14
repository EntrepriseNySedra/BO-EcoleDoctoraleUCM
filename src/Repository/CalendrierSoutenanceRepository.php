<?php

namespace App\Repository;

use App\Entity\CalendrierSoutenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierSoutenance|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierSoutenance|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierSoutenance[]    findAll()
 * @method CalendrierSoutenance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierSoutenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierSoutenance::class);
    }

    // /**
    //  * @return CalendrierSoutenance[] Returns an array of CalendrierSoutenance objects
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
    public function findOneBySomeField($value): ?CalendrierSoutenance
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
