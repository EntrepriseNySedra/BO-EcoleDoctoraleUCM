<?php

namespace App\Repository;

use App\Entity\ConcoursCandidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConcoursCandidature|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConcoursCandidature|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConcoursCandidature[]    findAll()
 * @method ConcoursCandidature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcoursCandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcoursCandidature::class);
    }

    // /**
    //  * @return ConcoursCandidature[] Returns an array of ConcoursCandidature objects
    //  */
    
    public function findByIds($ids=array())
    {
        return $this->createQueryBuilder('cc')
            ->andWhere('cc.id IN (:val)')
            ->setParameter('val', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?ConcoursCandidature
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
