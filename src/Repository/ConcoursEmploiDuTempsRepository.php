<?php

namespace App\Repository;

use App\Entity\ConcoursEmploiDuTemps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConcoursEmploiDuTemps|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConcoursEmploiDuTemps|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConcoursEmploiDuTemps[]    findAll()
 * @method ConcoursEmploiDuTemps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcoursEmploiDuTempsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcoursEmploiDuTemps::class);
    }

    // /**
    //  * @return ConcoursEmploiDuTemps[] Returns an array of ConcoursEmploiDuTemps objects
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
    public function findOneBySomeField($value): ?ConcoursEmploiDuTemps
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
