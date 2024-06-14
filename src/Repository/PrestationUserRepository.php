<?php

namespace App\Repository;

use App\Entity\PrestationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrestationUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrestationUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrestationUser[]    findAll()
 * @method PrestationUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrestationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrestationUser::class);
    }

    // /**
    //  * @return PrestationUser[] Returns an array of PrestationUser objects
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
    public function findOneBySomeField($value): ?PrestationUser
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
