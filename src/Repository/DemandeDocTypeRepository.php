<?php

namespace App\Repository;

use App\Entity\DemandeDocType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DemandeDocType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeDocType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeDocType[]    findAll()
 * @method DemandeDocType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeDocTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDocType::class);
    }

    // /**
    //  * @return DemandeDocType[] Returns an array of DemandeDocType objects
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
    public function findOneBySomeField($value): ?DemandeDocType
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
