<?php

namespace App\Repository;

use App\Entity\ConcoursNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConcoursNotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConcoursNotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConcoursNotes[]    findAll()
 * @method ConcoursNotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcoursNotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcoursNotes::class);
    }

    // /**
    //  * @return ConcoursNotes[] Returns an array of ConcoursNotes objects
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
    public function findOneBySomeField($value): ?ConcoursNotes
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
