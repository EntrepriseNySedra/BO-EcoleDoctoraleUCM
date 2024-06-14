<?php

namespace App\Repository;

use App\Entity\CoursMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoursMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursMedia[]    findAll()
 * @method CoursMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursMedia::class);
    }

    // /**
    //  * @return CoursMedia[] Returns an array of CoursMedia objects
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
    public function findOneBySomeField($value): ?CoursMedia
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
