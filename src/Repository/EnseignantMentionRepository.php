<?php

namespace App\Repository;

use App\Entity\EnseignantMention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EnseignantMention|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnseignantMention|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnseignantMention[]    findAll()
 * @method EnseignantMention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnseignantMentionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnseignantMention::class);
    }

    // /**
    //  * @return EnseignantMention[] Returns an array of EnseignantMention objects
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
    public function findOneBySomeField($value): ?EnseignantMention
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
