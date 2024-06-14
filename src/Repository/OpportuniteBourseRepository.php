<?php

namespace App\Repository;

use App\Entity\OpportuniteBourse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpportuniteBourse>
 *
 * @method OpportuniteBourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method OpportuniteBourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method OpportuniteBourse[]    findAll()
 * @method OpportuniteBourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OpportuniteBourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpportuniteBourse::class);
    }

    public function add(OpportuniteBourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OpportuniteBourse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OpportuniteBourse[] Returns an array of OpportuniteBourse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OpportuniteBourse
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
