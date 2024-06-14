<?php

namespace App\Repository;

use App\Entity\ConcoursMatiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConcoursMatiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConcoursMatiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConcoursMatiere[]    findAll()
 * @method ConcoursMatiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcoursMatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcoursMatiere::class);
    }

    // /**
    //  * @return ConcoursMatiere[] Returns an array of ConcoursMatiere objects
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
    public function findOneBySomeField($value): ?ConcoursMatiere
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
