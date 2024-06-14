<?php

namespace App\Repository;

use App\Entity\CorpsProfessoral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CorpsProfessoral>
 *
 * @method CorpsProfessoral|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorpsProfessoral|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorpsProfessoral[]    findAll()
 * @method CorpsProfessoral[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorpsProfessoralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorpsProfessoral::class);
    }

    public function add(CorpsProfessoral $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CorpsProfessoral $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CorpsProfessoral[] Returns an array of CorpsProfessoral objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CorpsProfessoral
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
