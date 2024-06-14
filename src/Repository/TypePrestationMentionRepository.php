<?php

namespace App\Repository;

use App\Entity\TypePrestationMention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypePrestationMention|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypePrestationMention|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypePrestationMention[]    findAll()
 * @method TypePrestationMention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypePrestationMentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePrestationMention::class);
    }

    // /**
    //  * @return TypePrestationMention[] Returns an array of TypePrestationMention objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypePrestationMention
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
