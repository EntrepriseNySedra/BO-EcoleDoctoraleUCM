<?php

namespace App\Repository;

use App\Entity\ExtraNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExtraNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtraNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtraNote[]    findAll()
 * @method ExtraNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtraNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtraNote::class);
    }

    // /**
    //  * @return ExtraNote[] Returns an array of ExtraNote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExtraNote
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
