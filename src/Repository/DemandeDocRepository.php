<?php

namespace App\Repository;

use App\Entity\DemandeDoc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DemandeDoc|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeDoc|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeDoc[]    findAll()
 * @method DemandeDoc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeDocRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDoc::class);
    }

    // /**
    //  * @return DemandeDoc[] Returns an array of DemandeDoc objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DemandeDoc
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
