<?php

namespace App\Repository;

use App\Entity\CalendrierExamen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalendrierExamen|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendrierExamen|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendrierExamen[]    findAll()
 * @method CalendrierExamen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendrierExamenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendrierExamen::class);
    }

    // /**
    //  * @return CalendrierExamen[] Returns an array of CalendrierExamen objects
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
    public function findOneBySomeField($value): ?CalendrierExamen
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
