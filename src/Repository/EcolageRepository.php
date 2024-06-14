<?php

namespace App\Repository;

use App\Entity\Ecolage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ecolage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ecolage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ecolage[]    findAll()
 * @method Ecolage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcolageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecolage::class);
    }

    // /**
    //  * @return Ecolage[] Returns an array of Ecolage objects
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
    public function findOneBySomeField($value): ?Ecolage
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
