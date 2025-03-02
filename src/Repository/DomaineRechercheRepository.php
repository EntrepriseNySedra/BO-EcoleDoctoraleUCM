<?php

namespace App\Repository;

use App\Entity\DomaineRecherche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DomaineRecherche>
 *
 * @method DomaineRecherche|null find($id, $lockMode = null, $lockVersion = null)
 * @method DomaineRecherche|null findOneBy(array $criteria, array $orderBy = null)
 * @method DomaineRecherche[]    findAll()
 * @method DomaineRecherche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomaineRechercheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomaineRecherche::class);
    }

    public function add(DomaineRecherche $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DomaineRecherche $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DomaineRecherche[] Returns an array of DomaineRecherche objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DomaineRecherche
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
