<?php

namespace App\Repository;

use App\Entity\TypeArticleEcole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeArticleEcole>
 *
 * @method TypeArticleEcole|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeArticleEcole|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeArticleEcole[]    findAll()
 * @method TypeArticleEcole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeArticleEcoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeArticleEcole::class);
    }

    public function add(TypeArticleEcole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeArticleEcole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TypeArticleEcole[] Returns an array of TypeArticleEcole objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TypeArticleEcole
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
