<?php

namespace App\Repository;

use App\Entity\RealisationAcademique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RealisationAcademique>
 *
 * @method RealisationAcademique|null find($id, $lockMode = null, $lockVersion = null)
 * @method RealisationAcademique|null findOneBy(array $criteria, array $orderBy = null)
 * @method RealisationAcademique[]    findAll()
 * @method RealisationAcademique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RealisationAcademiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RealisationAcademique::class);
    }

    public function add(RealisationAcademique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RealisationAcademique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RealisationAcademique[] Returns an array of RealisationAcademique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RealisationAcademique
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
