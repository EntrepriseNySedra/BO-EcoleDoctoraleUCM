<?php

namespace App\Repository;

use App\Entity\UniteEnseignements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UniteEnseignements|null find($id, $lockMode = null, $lockVersion = null)
 * @method UniteEnseignements|null findOneBy(array $criteria, array $orderBy = null)
 * @method UniteEnseignements[]    findAll()
 * @method UniteEnseignements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UniteEnseignementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UniteEnseignements::class);
    }

    // /**
    //  * @return UniteEnseignements[] Returns an array of UniteEnseignements objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UniteEnseignements
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
