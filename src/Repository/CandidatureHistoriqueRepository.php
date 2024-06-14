<?php

namespace App\Repository;

use App\Entity\CandidatureHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CandidatureHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method CandidatureHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method CandidatureHistorique[]    findAll()
 * @method CandidatureHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CandidatureHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidatureHistorique::class);
    }

    // /**
    //  * @return CandidatureHistorique[] Returns an array of CandidatureHistorique objects
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
    public function findOneBySomeField($value): ?CandidatureHistorique
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
