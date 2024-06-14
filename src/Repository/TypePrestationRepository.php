<?php

namespace App\Repository;

use App\Entity\TypePrestation;
use App\Entity\TypePrestationMention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypePrestation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypePrestation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypePrestation[]    findAll()
 * @method TypePrestation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypePrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePrestation::class);
    }

    // /**
    //  * @return TypePrestation[] Returns an array of TypePrestation objects
    //  */
    public function findByMention($mentionId)
    {
        return $this->createQueryBuilder('tp')
            ->innerJoin(TypePrestationMention::class, 'tpm')
            ->andWhere('tpm.mention = :mentionId')
            ->setParameter('mentionId', $mentionId)
            ->orderBy('tp.designation', 'ASC')
            ->setMaxResults(500)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?TypePrestation
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
