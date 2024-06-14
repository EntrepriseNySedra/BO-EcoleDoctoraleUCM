<?php

namespace App\Repository;

use App\Entity\PaiementHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaiementHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementHistory[]    findAll()
 * @method PaiementHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementHistory::class);
    }

    // /**
    //  * @return PaiementHistory[] Returns an array of PaiementHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaiementHistory
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
